<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->get();
        return response()->json($users);
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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);
        $user = User::findOrFail($validated['user_id']);
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }
        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }
        return response()->json($user->load(['roles', 'permissions']));
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|exists:roles,name',
            'permission' => 'nullable|exists:permissions,name',
        ]);
        $user = User::findOrFail($validated['user_id']);
        if (isset($validated['role'])) {
            $user->removeRole($validated['role']);
        }
        if (isset($validated['permission'])) {
            $user->revokePermissionTo($validated['permission']);
        }
        return response()->json($user->load(['roles', 'permissions']));
    }
}
