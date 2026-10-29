<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\Menu;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index', ['roles' => Role::withCount('users')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('roles.form', ['role' => new Role, 'groups' => Menu::permissionGroups()]);
    }

    public function store(Request $r)
    {
        Role::create($this->data($r, null));

        return redirect()->route('roles.index')->with('ok', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('roles.form', ['role' => $role, 'groups' => Menu::permissionGroups()]);
    }

    public function update(Request $r, Role $role)
    {
        $role->update($this->data($r, $role->id));

        return redirect()->route('roles.index')->with('ok', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_admin || $role->users()->exists()) {
            return back()->withErrors(['delete' => 'Admin role or roles with users cannot be deleted.']);
        }

        $role->delete();

        return back()->with('ok', 'Role deleted.');
    }

    private function data(Request $r, ?int $id): array
    {
        $data = $r->validate([
            'name'          => 'required|string|max:100|unique:roles,name,' . ($id ?? 'NULL'),
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        return [
            'name'        => $data['name'],
            'is_admin'    => $r->boolean('is_admin'),
            'permissions' => array_values($data['permissions'] ?? []),
        ];
    }
}
