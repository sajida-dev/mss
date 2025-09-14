<?php

namespace App\Observers;

use Spatie\Permission\Models\Permission;
use App\Models\User;
use Modules\Schools\App\Models\School;
use Spatie\Permission\PermissionRegistrar;

class PermissionObserver
{
    public function created(Permission $permission)
    {
        // $admin = User::where('username', 'admin')->first();
        // if ($admin) {
        //     $schools = School::all();
        //     foreach ($schools as $school) {
        //         // Set team context for this school
        //         app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        //         // Check if permission is already assigned to avoid duplicates
        //         if (!$admin->hasPermissionTo($permission->name)) {
        //             $admin->givePermissionTo($permission->name);
        //         }
        //     }
        //     // Reset team context
        //     app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        // }
    }
}
