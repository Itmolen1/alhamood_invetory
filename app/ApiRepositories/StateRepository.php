<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IStateRepositoryInterface;
use App\Http\Requests\StateRequest;
use App\Http\Resources\State\StateResource;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StateRepository implements IStateRepositoryInterface
{
    public function all()
    {
        return StateResource::collection(State::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return StateResource::Collection(State::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $country = new State();
        $country->Name=$request->Name;
        $country->country_id=$request->country_id;
        $country->createdDate=date('Y-m-d h:i:s');
        $country->isActive=1;
        $country->user_id = $userId ?? 0;
        $country->save();
        return new StateResource(State::find($country->id));
    }

    public function update(StateRequest $stateRequest, $Id)
    {
        $userId = Auth::id();
        $country = State::find($Id);
        $stateRequest['user_id']=$userId ?? 0;
        $country->update($stateRequest->all());
        return new StateResource(State::find($Id));
    }

    public function getById($Id)
    {
        return new StateResource(State::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = State::find($Id);
        $update->user_id=$userId;
        $update->save();
        $country = State::withoutTrashed()->find($Id);
        if($country->trashed())
        {
            return new StateResource(State::onlyTrashed()->find($Id));
        }
        else
        {
            $country->delete();
            return new StateResource(State::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $country = State::onlyTrashed()->find($Id);
        if (!is_null($country))
        {
            $country->restore();
            return new StateResource(State::find($Id));
        }
        return new StateResource(State::find($Id));
    }

    public function trashed()
    {
        $country = State::onlyTrashed()->get();
        return StateResource::collection($country);
    }
}
