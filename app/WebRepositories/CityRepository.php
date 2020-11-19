<?php


namespace App\WebRepositories;


use App\Http\Requests\CityRequest;
use App\Models\City;
use App\Models\State;
use App\WebRepositories\Interfaces\ICityRepositoryInterface;
use Illuminate\Http\Request;

class CityRepository implements ICityRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $cities = City::with('user','state')->get();
        //dd($states[0]->country->id);
        return view('admin.city.index',compact('cities'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $states = State::all();
        return view('admin.city.create',compact('states'));
    }

    public function store(CityRequest $cityRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $city = [
            'Name' =>$cityRequest->Name,
            'state_id' =>$cityRequest->state_id,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        City::create($city);
        return redirect()->route('cities.index');
    }

    public function update(Request $request, $Id)
    {
        //dd($request->all());
        // TODO: Implement update() method.
        $city = City::find($Id);
        $user_id = session('user_id');
        $city->update([
            'Name' =>$request->Name,
            'state_id' =>$request->state_id,
            'user_id' =>$user_id,
        ]);
        return redirect()->route('cities.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $states = State::all();
        $city = City::find($Id);
        return view('admin.city.edit',compact('states','city'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = City::findOrFail($Id);
        $data->delete();
        return redirect()->route('cities.index');
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
