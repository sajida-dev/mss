<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UpdateRolePermissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $roleId;
    protected $permissions;
    /**
     * Create a new job instance.
     */


    public function __construct(int $roleId, array $permissions)
    {
        $this->roleId = $roleId;
        $this->permissions = $permissions;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $role = Role::findOrFail($this->roleId);

        // Set team id context for Spatie multi-tenant permission package
        app(PermissionRegistrar::class)->setPermissionsTeamId($role->school_id);

        $role->syncPermissions($this->permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
