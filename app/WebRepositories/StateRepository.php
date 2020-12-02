<?php


namespace App\WebRepositories;


use App\Http\Requests\StateRequest;
use App\Models\Country;
use App\Models\State;
use App\WebRepositories\Interfaces\IStatesRepositoryInterface;
use Illuminate\Http\Request;

class StateRepository implements IStatesRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $states = State::with('user','country')->get();
        //dd($states[0]->country->id);
        return view('admin.state.index',compact('states'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $countries = Country::all();
        return view('admin.state.create',compact('countries'));
    }

    public function store(StateRequest $stateRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $state = [
            'Name' =>$stateRequest->Name,
            'country_id' =>$stateRequest->country_id ?? 0,
            'user_id' =>$user_id ?? 0,
            'company_id' =>$company_id ?? 0,
        ];
        State::create($state);
        return redirect()->route('states.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $state = State::find($Id);
        $user_id = session('user_id');
        $state->update([
            'Name' =>$request->Name,
            'country_id' =>$request->country_id ?? 0,
            'user_id' =>$user_id ?? 0,
        ]);
        return redirect()->route('states.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $countries = Country::all();
        $state = State::find($Id);
        return view('admin.state.edit',compact('state','countries'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = State::findOrFail($Id);
        $data->delete();
        return redirect()->route('states.index');
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
