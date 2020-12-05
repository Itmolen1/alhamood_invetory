<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICityRepositoryInterface;
use App\Http\Requests\CityRequest;
use App\Http\Resources\City\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityRepository implements ICityRepositoryInterface
{
    public function all()
    {
        return CityResource::collection(City::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CityResource::Collection(City::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(CityRequest $cityRequest)
    {
        $userId = Auth::id();
        $city = new City();
        $city->Name=$cityRequest->Name;
        $city->state_id=$cityRequest->state_id;
        $city->createdDate=date('Y-m-d h:i:s');
        $city->isActive=1;
        $city->user_id = $userId ?? 0;
        $city->save();
        return new CityResource(City::find($city->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $city = City::find($Id);
        $request['user_id']=$userId ?? 0;
        $city->update($request->all());
        return new CityResource(City::find($Id));
    }

    public function getById($Id)
    {
        return new CityResource(City::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = City::find($Id);
        $update->user_id=$userId;
        $update->save();
        $city = City::withoutTrashed()->find($Id);
        if($city->trashed())
        {
            return new CityResource(City::onlyTrashed()->find($Id));
        }
        else
        {
            $city->delete();
            return new CityResource(City::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $city = City::onlyTrashed()->find($Id);
        if (!is_null($city))
        {
            $city->restore();
            return new CityResource(City::find($Id));
        }
        return new CityResource(City::find($Id));
    }

    public function trashed()
    {
        $city = City::onlyTrashed()->get();
        return CityResource::collection($city);
    }

    public function ActivateDeactivate($Id)
    {
        $city = City::find($Id);
        if($city->isActive==1)
        {
            $city->isActive=0;
        }
        else
        {
            $city->isActive=1;
        }
        $city->update();
        return new CityResource(City::find($Id));
    }
}
