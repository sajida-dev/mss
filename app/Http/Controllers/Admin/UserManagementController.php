<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Schools\App\Models\School;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserPassword;

class UserManagementController extends Controller
{
    /**
     * Helper method to set school context for role operations
     */
    private function setSchoolContextForRoles($schoolId)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
    }

    /**
     * Helper method to clear school context
     */
    private function clearSchoolContext()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    /**
     * Display a listing of users with their roles
     */
    public function index(Request $request)
    {
        Log::info('User management index request', [
            'filters' => $request->all(),
        ]);

        $query = User::with(['roles' => function ($query) {
            // Remove the school relationship loading since it doesn't exist on Role model
        }]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // Get paginated results
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Process users to include roles_by_school and passwords
        $users->getCollection()->transform(function ($user) {
            // Get all schools
            $schools = \Modules\Schools\App\Models\School::all();
            $rolesBySchool = [];

            foreach ($schools as $school) {
                // Set team context for this school
                $this->setSchoolContextForRoles($school->id);

                // Get user's roles for this school
                $userRolesForSchool = $user->roles()->get();

                if ($userRolesForSchool->count() > 0) {
                    $rolesBySchool[$school->name] = $userRolesForSchool->map(function ($role) use ($school) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'school_id' => $role->school_id,
                            'school_name' => $school->name,
                        ];
                    })->toArray();
                }
            }

            // Reset team context
            $this->clearSchoolContext();

            // Add roles_by_school to user
            $user->roles_by_school = $rolesBySchool;

            $user->password = '••••••••••••'; // Default hidden password
            $user->show_password = false;

            return $user;
        });

        // Get all roles for filtering
        $roles = Role::get()->map(function ($role) {
            $schoolName = null;
            if ($role->school_id) {
                $school = \Modules\Schools\App\Models\School::find($role->school_id);
                $schoolName = $school ? $school->name : null;
            }

            return [
                'id' => $role->id,
                'name' => $role->name,
                'school_id' => $role->school_id,
                'school_name' => $schoolName,
            ];
        });

        // Get all schools for the school selector
        $schools = \Modules\Schools\App\Models\School::all();

        Log::info('User management index response', [
            'users_count' => $users->count(),
            'roles_count' => $roles->count(),
            'schools_count' => $schools->count(),
        ]);

        return Inertia::render('settings/Users', [
            'users' => $users,
            'roles' => $roles,
            'schools' => $schools,
            'filters' => [
                'search' => $request->search ?? '',
                'role' => $request->role ?? '',
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Display the add user page
     */
    public function addUserPage()
    {
        $roles = Role::where('name', '!=', 'superadmin')
            ->select('id', 'name', 'school_id')
            ->get()
            ->map(function ($role) {
                $schoolName = 'Global';
                if ($role->school_id) {
                    $school = \Modules\Schools\App\Models\School::find($role->school_id);
                    $schoolName = $school ? $school->name : 'Unknown School';
                }

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'school_id' => $role->school_id,
                    'school_name' => $schoolName
                ];
            });

        // Get all schools for assign role modal
        $schools = School::select('id', 'name')->get();

        return Inertia::render('settings/AddUser', [
            'roles' => $roles,
            'schools' => $schools,
        ]);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        Log::info('User creation request received', [
            'name' => $request->name,
            'email' => $request->email,
            'role_name' => $request->role_name,
            'school_id' => $request->school_id,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'role_name' => 'required|string',
            'school_id' => 'required|exists:schools,id',
        ]);

        $roleName = $request->role_name;
        $schoolId = $request->school_id;

        if ($roleName === 'superadmin') {
            Log::warning('Attempted to assign superadmin role during user creation');
            return redirect()->back()->withErrors(['role_name' => 'Super admin role cannot be assigned manually.']);
        }

        $role = Role::where('name', $roleName)
            ->where('guard_name', 'web')
            ->where('school_id', $schoolId)
            ->first();

        if (!$role) {
            Log::error('Role not found for school during user creation', [
                'role_name' => $roleName,
                'school_id' => $schoolId,
            ]);
            return redirect()->back()->withErrors(['role_name' => 'Role does not exist for the selected school.']);
        }

        // Use provided password or generate a random one
        $password = $request->filled('password') ? $request->password : Str::random(12);

        // Handle file upload
        $profilePhotoPath = 'default-profile.png';
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('profile-photos', $fileName, 'public');
            $profilePhotoPath = $filePath;
        }

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'profile_photo_path' => $profilePhotoPath,
            'email_verified_at' => now(),
        ];

        $user = PasswordService::createUserWithPassword($userData, $password);

        $this->setSchoolContextForRoles($schoolId);
        $user->assignRole($role->name);
        $this->clearSchoolContext();

        $school = \Modules\Schools\App\Models\School::find($schoolId);
        $message = "User '{$user->name}' created successfully with role '{$role->name}' for {$school->name}. Temporary password: {$password}";
        Log::info('User creation completed', [
            'user_id' => $user->id,
            'message' => $message
        ]);

        return redirect()->back()->with([
            'success' => $message,
            'user_password' => $password // Include password in flash data
        ]);
    }

    /**
     * Securely return decrypted password for a user (admin only)
     */
    public function getUserPassword(Request $request, $id = null)
    {
        Log::info('getUserPassword called', [
            'requested_user_id' => $id,
            'auth_user_id' => $request->user() ? $request->user()->id : null,
            'is_superadmin' => $request->user() && $request->user()->hasRole('superadmin'),
        ]);

        // Accept user ID from route param or request
        $userId = $id ?? $request->input('user_id');
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only allow superadmin or users with explicit permission
        if (!$admin->hasRole('superadmin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $result = PasswordService::getUserPassword($userId);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 404);
        }

        return response()->json(['password' => $result['password']]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        Log::info('Role assignment request received', [
            'user_id' => $request->user_id,
            'role_name' => $request->role_name,
            'school_id' => $request->school_id,
        ]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|string',
            'school_id' => 'required|exists:schools,id', // School is always required
        ]);

        $user = User::findOrFail($request->user_id);
        $schoolId = $request->school_id;
        $roleName = $request->role_name;

        Log::info('Found user and school', [
            'user_name' => $user->name,
            'school_id' => $schoolId,
            'role_name' => $roleName,
        ]);

        // Prevent super admin role assignment
        if ($roleName === 'superadmin') {
            Log::warning('Attempted to assign superadmin role');
            return redirect()->back()->withErrors(['role_name' => 'Super admin role cannot be assigned manually.']);
        }

        // Find the role for this school (do not create)
        $role = Role::where('name', $roleName)
            ->where('guard_name', 'web')
            ->where('school_id', $schoolId)
            ->first();

        Log::info('Role lookup result', [
            'role_found' => $role ? true : false,
            'role_id' => $role?->id,
            'role_school_id' => $role?->school_id,
        ]);

        if (!$role) {
            Log::error('Role not found for school', [
                'role_name' => $roleName,
                'school_id' => $schoolId,
            ]);
            return redirect()->back()->withErrors(['role_name' => 'Role does not exist for the selected school.']);
        }

        // Set Spatie team context for the target school
        $this->setSchoolContextForRoles($schoolId);

        // Check if user already has this role for this school
        $alreadyHasRole = $user->hasRole($role->name);
        Log::info('Role assignment check', [
            'user_has_role' => $alreadyHasRole,
        ]);

        // Assign the role to the user for this school
        if (!$alreadyHasRole) {
            $user->assignRole($role->name);
            Log::info('Role assigned successfully');
        } else {
            Log::info('User already has this role for this school');
            return redirect()->back()->with('info', "User already has role '{$role->name}' for this school.");
        }

        // Reset team context
        $this->clearSchoolContext();

        $school = \Modules\Schools\App\Models\School::find($schoolId);
        $message = "Role '{$role->name}' assigned to {$user->name} for {$school->name} successfully";

        Log::info('Role assignment completed', ['message' => $message]);

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Check if user is trying to remove admin role from themselves
        if ($request->user()->id === $user->id && ($role->name === 'superadmin' || $role->name === 'admin')) {
            return redirect()->back()->with('error', 'You cannot remove admin role from yourself');
        }

        // Check if this is the last super admin user
        if ($role->name === 'superadmin' && $user->hasRole('superadmin')) {
            $adminCount = User::role('superadmin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'Cannot remove the last super admin user');
            }
        }

        // Set team context based on role's school_id
        if ($role->school_id) {
            $this->setSchoolContextForRoles($role->school_id);
        } else {
            $this->clearSchoolContext();
        }

        // Remove the role
        $user->removeRole($role->name);

        // Reset team context
        $this->clearSchoolContext();

        return redirect()->back()->with('success', "Role '{$role->name}' removed from {$user->name} successfully");
    }

    /**
     * Verify current user's password for security
     */
    public function verifyPassword(Request $request)
    {
        Log::info('Password verification request received', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
        ]);

        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $providedPassword = $request->password;

        Log::info('Password verification attempt', [
            'user_id' => $user->id,
            'password_provided' => !empty($providedPassword),
            'password_length' => strlen($providedPassword),
        ]);

        // Verify current user's password
        if (!Hash::check($providedPassword, $user->password)) {
            Log::warning('Password verification failed', [
                'user_id' => $user->id,
                'reason' => 'Hash check failed'
            ]);
            return redirect()->back()->withErrors(['password' => 'Invalid password']);
        }

        Log::info('Password verification successful', [
            'user_id' => $user->id
        ]);

        return redirect()->back()->with('password_verified', true);
    }

    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'admin_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        $admin = $request->user();
        if (!Hash::check($request->admin_password, $admin->password)) {
            return redirect()->back()->withErrors(['admin_password' => 'Invalid admin password']);
        }

        $result = PasswordService::resetUserPassword($request->user_id, $request->new_password);
        dd($result);
        return redirect()->back()->with('success', 'Password reset successfully');
        // return response()->json(['new_password' => $result['new_password']]);
    }
}
