<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IRegionRepositoryInterface;
use App\Http\Requests\RegionRequest;
use App\Http\Resources\Region\RegionResource;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegionRepository implements IRegionRepositoryInterface
{
    public function all()
    {
        return RegionResource::collection(Region::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return RegionResource::Collection(Region::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $region = new Region();
        $region->Name=$request->Name;
        $region->city_id=$request->city_id;
        $region->createdDate=date('Y-m-d h:i:s');
        $region->isActive=1;
        $region->user_id = $userId ?? 0;
        $region->save();
        return new RegionResource(Region::find($region->id));
    }

    public function update(RegionRequest $regionRequest, $Id)
    {
        $userId = Auth::id();
        $region = Region::find($Id);
        $regionRequest['user_id']=$userId ?? 0;
        $region->update($regionRequest->all());
        return new RegionResource(Region::find($Id));
    }

    public function getById($Id)
    {
        return new RegionResource(Region::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Region::find($Id);
        $update->user_id=$userId;
        $update->save();
        $region = Region::withoutTrashed()->find($Id);
        if($region->trashed())
        {
            return new RegionResource(Region::onlyTrashed()->find($Id));
        }
        else
        {
            $region->delete();
            return new RegionResource(Region::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $region = Region::onlyTrashed()->find($Id);
        if (!is_null($region))
        {
            $region->restore();
            return new RegionResource(Region::find($Id));
        }
        return new RegionResource(Region::find($Id));
    }

    public function trashed()
    {
        $region = Region::onlyTrashed()->get();
        return RegionResource::collection($region);
    }

    public function ActivateDeactivate($Id)
    {
        $region = Region::find($Id);
        if($region->isActive==1)
        {
            $region->isActive=0;
        }
        else
        {
            $region->isActive=1;
        }
        $region->update();
        return new RegionResource(Region::find($Id));
    }
}
