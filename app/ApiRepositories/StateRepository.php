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
        $state = new State();
        $state->Name=$request->Name;
        $state->country_id=$request->country_id;
        $state->createdDate=date('Y-m-d h:i:s');
        $state->isActive=1;
        $state->user_id = $userId ?? 0;
        $state->save();
        return new StateResource(State::find($state->id));
    }

    public function update(StateRequest $stateRequest, $Id)
    {
        $userId = Auth::id();
        $state = State::find($Id);
        $stateRequest['user_id']=$userId ?? 0;
        $state->update($stateRequest->all());
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
        $state = State::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return new StateResource(State::onlyTrashed()->find($Id));
        }
        else
        {
            $state->delete();
            return new StateResource(State::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $state = State::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return new StateResource(State::find($Id));
        }
        return new StateResource(State::find($Id));
    }

    public function trashed()
    {
        $state = State::onlyTrashed()->get();
        return StateResource::collection($state);
    }

    public function ActivateDeactivate($Id)
    {
        $state = State::find($Id);
        if($state->isActive==1)
        {
            $state->isActive=0;
        }
        else
        {
            $state->isActive=1;
        }
        $state->update();
        return new StateResource(State::find($Id));
    }
}
