<?php


namespace App\WebRepositories;


use App\Models\Role;
use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use Illuminate\Http\Request;

class RoleRepository implements  IRoleRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $roles = Role::all();
        return view('admin.role.index',compact('roles'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        return view('admin.role.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store() method.
        $role = [
            'Name' =>$request->Name,
        ];
         Role::create($role);
        return redirect()->route('roles.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $role = Role::find($Id);
        $role->update($request->all());
        return redirect()->route('roles.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $role = Role::find($Id);
        return view('admin.role.edit',compact('role'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Role::findOrFail($Id);
        $data->delete();
        return redirect()->route('roles.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
