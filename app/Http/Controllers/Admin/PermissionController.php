<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Permission::all());
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
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'nullable|string',
        ]);
        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Permission created successfully!',
                'permission' => $permission
            ]);
        }
        if ($request->expectsJson() && !$request->hasHeader('X-Inertia')) {
            return response()->json($permission, 201);
        }
        return redirect()->back()->with([
            'success' => 'Permission created successfully!',
            'permission' => $permission
        ]);
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
        $permission = Permission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'guard_name' => 'nullable|string',
        ]);
        $permission->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? $permission->guard_name,
        ]);
        return response()->json($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        if (request()->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Permission deleted successfully!'
            ]);
        }
        if (request()->expectsJson() && !request()->hasHeader('X-Inertia')) {
            return response()->json(['message' => 'Permission deleted']);
        }
        return redirect()->back()->with([
            'success' => 'Permission deleted successfully!'
        ]);
    }
}
