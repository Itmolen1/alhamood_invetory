<?php


namespace App\WebRepositories;


use App\Http\Requests\CompanyTypeRequest;
use App\Models\CompanyType;
use App\WebRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use Illuminate\Http\Request;

class CompanyTypeRepository implements ICompanyTypeRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        if(request()->ajax())
        {
            return datatables()->of(CompanyType::latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('company_types.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('company_types.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                   ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="" method="POST"  id="">';
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="" method="POST"  id="">';
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                ->rawColumns(['action','isActive'])
                ->make(true);
        }
        return view('admin.company_type.index');
    }

    public function create()
    {
        // TODO: Implement create() method.
         return view('admin.company_type.create');
    }

    public function store(CompanyTypeRequest $companyTypeRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = [
            'Name' => $companyTypeRequest->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
         CompanyType::create($data);
        return redirect()->route('company_types.index')->with('success','Record Inerted successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = CompanyType::find($Id);
        $data->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('company_types.index')->with('update','Record updated successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $company_type = CompanyType::find($Id);
        return view('admin.company_type.edit',compact('company_type'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $Update = CompanyType::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $data = CompanyType::withoutTrashed()->find($Id);
        if($data->trashed())
        {
            return redirect()->route('company_types.index');
        }
        else
        {
            $data->delete();
            return redirect()->route('company_types.index')->with('delete','Record Update Successfully');
        }
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
        $data = CompanyType::onlyTrashed()->find($Id);
        if (!is_null($data))
        {
            $data->restore();
            return redirect()->route('company_types.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('company_types.index');
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
        $trashes = CompanyType::with('user')->onlyTrashed()->get();
        return view('admin.company_type.edit',compact('trashes'));
    }
}
