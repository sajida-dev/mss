<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Modules\Schools\App\Models\School;

class TestRoleAssignment extends Command
{
    protected $signature = 'test:role-assignment';
    protected $description = 'Test role assignment functionality';

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

    public function handle()
    {
        $this->info('=== TESTING ROLE ASSIGNMENT ===');

        // Test 1: Check if schools exist
        $this->info('1. Checking schools...');
        $schools = School::all();
        foreach ($schools as $school) {
            $this->line("   - School ID: {$school->id}, Name: {$school->name}");
        }

        // Test 2: Check if roles exist for schools
        $this->info('2. Checking roles by school...');
        $roles = Role::where('name', '!=', 'superadmin')->get();
        foreach ($roles as $role) {
            $schoolName = $role->school_id ? School::find($role->school_id)?->name : 'Global';
            $this->line("   - Role: {$role->name} (School: {$schoolName})");
        }

        // Test 3: Check if users exist
        $this->info('3. Checking users...');
        $users = User::take(3)->get();
        foreach ($users as $user) {
            $this->line("   - User ID: {$user->id}, Name: {$user->name}");
        }

        // Test 4: Test role assignment for first user and first school
        if ($users->count() > 0 && $schools->count() > 0) {
            $this->info('4. Testing role assignment...');
            $user = $users->first();
            $school = $schools->first();

            // Find a role for this school
            $role = Role::where('school_id', $school->id)->first();

            if ($role) {
                $this->line("   - Assigning role '{$role->name}' to user '{$user->name}' for school '{$school->name}'");

                // Set team context
                $this->setSchoolContextForRoles($school->id);

                // Check if user already has this role
                if ($user->hasRole($role->name)) {
                    $this->line("   - User already has this role");
                } else {
                    // Assign the role
                    $user->assignRole($role->name);
                    $this->line("   - Role assigned successfully");
                }

                // Reset team context
                $this->clearSchoolContext();

                // Check the result
                $user->load('roles');
                $this->line("   - User roles after assignment: " . $user->roles->pluck('name')->join(', '));
            } else {
                $this->error("   - No roles found for school '{$school->name}'");
            }
        }

        $this->info('=== TEST COMPLETE ===');
        return 0;
    }
}
