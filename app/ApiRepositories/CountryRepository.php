<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICountryRepositoryInterface;
use App\Http\Requests\CountryRequest;
use App\Http\Resources\Country\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CountryRepository implements ICountryRepositoryInterface
{
    public function all()
    {
        return CountryResource::collection(Country::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CountryResource::Collection(Country::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $country = new Country();
        $country->Name=$request->Name;
        $country->shortForm=$request->shortForm;
        $country->createdDate=date('Y-m-d h:i:s');
        $country->isActive=1;
        $country->user_id = $userId ?? 0;
        $country->save();
        return new CountryResource(Country::find($country->id));
    }

    public function update(CountryRequest $countryRequest, $Id)
    {
        $userId = Auth::id();
        $country = Country::find($Id);
        $countryRequest['user_id']=$userId ?? 0;
        $country->update($countryRequest->all());
        return new CountryResource(Country::find($Id));
    }

    public function getById($Id)
    {
        return new CountryResource(Country::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Country::find($Id);
        $update->user_id=$userId;
        $update->save();
        $country = Country::withoutTrashed()->find($Id);
        if($country->trashed())
        {
            return new CountryResource(Country::onlyTrashed()->find($Id));
        }
        else
        {
            $country->delete();
            return new CountryResource(Country::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $country = Country::onlyTrashed()->find($Id);
        if (!is_null($country))
        {
            $country->restore();
            return new CountryResource(Country::find($Id));
        }
        return new CountryResource(Country::find($Id));
    }

    public function trashed()
    {
        $country = Country::onlyTrashed()->get();
        return CountryResource::collection($country);
    }

    public function ActivateDeactivate($Id)
    {
        $country = Country::find($Id);
        if($country->isActive==1)
        {
            $country->isActive=0;
        }
        else
        {
            $country->isActive=1;
        }
        $country->update();
        return new CountryResource(Country::find($Id));
    }
}
