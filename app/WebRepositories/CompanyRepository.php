<?php


namespace App\WebRepositories;


use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyRepository implements ICompanyRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $companies = Company::all();
        return view('admin.company.index',compact('companies'));
    }

    public function create()
    {
        // TODO: Implement create() method.

//        dd(Auth::user()->name);
//        $user_id = session('user_id');
//        dd($user_id);
        $regions = Region::with('city')->get();
        return view('admin.company.create',compact('regions'));

    }

    public function store(CompanyRequest $companyRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company = [
            'Name' =>$companyRequest->Name,
            'Mobile' =>$companyRequest->Mobile,
            'Representative' =>$companyRequest->Representative,
            'Phone' =>$companyRequest->Phone,
            'Address' =>$companyRequest->Address,
            'region_id' =>$companyRequest->region_id,
            'postCode' =>$companyRequest->postCode,
            'user_id' =>$user_id,
            'Description' =>$companyRequest->Description,
        ];
        Company::create($company);
        return redirect()->route('companies.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $company = Company::find($Id);
        $user_id = session('user_id');
        $company->update([
            'Name' => $request->Name,
            'Phone' => $request->Phone,
            'Mobile' => $request->Mobile,
            'Representative' => $request->Representative,
            'Address' => $request->Address,
            'region_id' =>$request->region_id,
            'postCode' => $request->postCode,
            'Description' => $request->Description,
            'user_id' => $user_id,

        ]);
        return redirect()->route('companies.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $regions = Region::with('city')->get();
        $company = Company::with('region')->find($Id);
        return view('admin.company.edit',compact('company','regions'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Company::findOrFail($Id);
        $data->delete();
        return redirect()->route('companies.index');
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
