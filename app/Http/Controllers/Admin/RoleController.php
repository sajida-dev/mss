<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load permissions and school data for each role
        $roles = Role::with('permissions')
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
                    'school_name' => $schoolName,
                    'permissions' => $role->permissions
                ];
            });

        return response()->json($roles);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */



    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('roles')->where(fn($query) => $query->where('school_id', $request->input('school_id'))),
                ],
                'school_id' => 'nullable|exists:schools,id',
                'guard_name' => 'nullable|string',
            ]);
            $school_id = session('active_school_id') ?? $validated['school_id'];
            // Set the current school/team context for Spatie permission package
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($school_id ?? null);

            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => $validated['guard_name'] ?? 'web',
                'school_id' => $validated['school_id'] ?? null,
            ]);

            DB::commit();

            if ($request->hasHeader('X-Inertia')) {
                return redirect()->back()->with([
                    'success' => 'Role created successfully!',
                    'role' => $role,
                ]);
            }

            if ($request->expectsJson() && !$request->hasHeader('X-Inertia')) {
                return response()->json($role, 201);
            }

            return redirect()->back()->with([
                'success' => 'Role created successfully!',
                'role' => $role,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            if ($request->expectsJson() || $request->hasHeader('X-Inertia')) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed: ' . $e->getMessage());

            $message = 'An error occurred while creating the role. Please try again.';
            if ($request->expectsJson() || $request->hasHeader('X-Inertia')) {
                return redirect()->back()->with('error', $message);
            }
            return response()->json(['error' => $message], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($role->school_id);
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $role->load('permissions');

        // Always check for Inertia first, and never return JSON for Inertia
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Permissions updated successfully!',
                'role' => $role
            ]);
        }

        // Only return JSON for true API requests (no X-Inertia)
        if ($request->expectsJson() && !$request->hasHeader('X-Inertia')) {
            return response()->json($role);
        }

        // Fallback: always redirect for web requests
        return redirect()->back()->with([
            'success' => 'Permissions updated successfully!',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        if (request()->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Role deleted successfully!'
            ]);
        }
        if (request()->expectsJson() && !request()->hasHeader('X-Inertia')) {
            return response()->json(['message' => 'Role deleted']);
        }
        return redirect()->back()->with([
            'success' => 'Role deleted successfully!'
        ]);
    }
}
