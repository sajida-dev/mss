<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Get roles for dropdown/select components
     * This is a utility method for other parts of the application
     */
    public function roles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }
}
