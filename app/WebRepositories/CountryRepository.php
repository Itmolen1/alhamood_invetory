<?php


namespace App\WebRepositories;


use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\WebRepositories\Interfaces\ICountryRepositoryInterface;
use Illuminate\Http\Request;

class CountryRepository implements ICountryRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $countries = Country::all();
        return view('admin.country.index',compact('countries'));
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(CountryRequest $countryRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $country = [
            'Name' =>$countryRequest->Name,
            'shortForm' =>$countryRequest->shortForm,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        Country::create($country);
        return redirect()->route('countries.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $country = Country::find($Id);
        $user_id = session('user_id');
        $country->update([
            'Name' =>$request->Name,
            'shortForm' =>$request->shortForm,
            'user_id' =>$user_id,
        ]);
        return redirect()->route('countries.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $country = Country::find($Id);
        return view('admin.country.edit',compact('country'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Country::findOrFail($Id);
        $data->delete();
        return redirect()->route('countries.index');
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
