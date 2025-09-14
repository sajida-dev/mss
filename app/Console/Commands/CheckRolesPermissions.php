<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:roles-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check roles and permissions in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ROLES ===');
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->line($role->name . ' (school_id: ' . ($role->school_id ?? 'null') . ')');
        }

        $this->info('=== PERMISSIONS ===');
        $permissions = Permission::take(5)->get();
        foreach ($permissions as $permission) {
            $this->line($permission->name);
        }

        $this->info('=== USERS WITH ROLES ===');
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $roleNames = $user->roles->pluck('name')->join(', ');
            $this->line($user->name . ': ' . $roleNames);
        }

        $this->info('=== MODEL HAS ROLES ===');
        $modelHasRoles = DB::table('model_has_roles')->get();
        foreach ($modelHasRoles as $record) {
            $this->line("User ID: {$record->model_id}, Role ID: {$record->role_id}, School ID: " . ($record->school_id ?? 'null'));
        }

        $this->info('=== MODEL HAS PERMISSIONS ===');
        $modelHasPermissions = DB::table('model_has_permissions')->get();
        if ($modelHasPermissions->isEmpty()) {
            $this->line('No direct permission assignments (this is normal - permissions are assigned to roles)');
        } else {
            foreach ($modelHasPermissions as $record) {
                $this->line("User ID: {$record->model_id}, Permission ID: {$record->permission_id}, School ID: " . ($record->school_id ?? 'null'));
            }
        }

        return 0;
    }
}
