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
        if(request()->ajax())
        {
            return datatables()->of(Role::latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('roles.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('roles.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="" method="POST"  id="">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="" method="POST"  id="">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
            
                ->rawColumns([
                    'action',
                    'isActive',
                ])
                ->make(true);
        }
        return view('admin.role.index');
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
        $role->update([
            'Name' => $request->Name,
        ]);
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
