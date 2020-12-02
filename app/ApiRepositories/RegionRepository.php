<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IRegionRepositoryInterface;
use App\Http\Requests\RegionRequest;
use App\Http\Resources\Region\RegionResource;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function get_detail_list()
    {
        $country = DB::table('countries')->select(
            'id',
            'Name'
        )->where('deleted_at',NULL)->get();
        $country = json_decode(json_encode($country), true);
        //echo "<pre>";print_r($country);die;
        for($i=0;$i<count($country);$i++)
        {
            $state = DB::table('states as s')->select(
                's.id',
                's.Name',
                's.country_id',
                'c.Name as country_name'
            )->where([['s.deleted_at',NULL],['s.id',$country[$i]['id']]])->leftjoin('countries as c', 'c.id', '=', 's.id')->get();
            $state = json_decode(json_encode($state), true);
            //echo "<pre>";print_r($state);die;
            for($j=0;$j<count($state);$j++)
            {
                $state_id_here=$state[$j]['id'];
                $city = DB::table('cities as ct')->select(
                    'ct.id',
                    'ct.Name',
                    'ct.state_id',
                    's.Name as state_name'
                )->where([['ct.deleted_at',NULL],['ct.state_id',$state_id_here]])
                    ->leftjoin('states as s', 'ct.state_id', '=', 's.id')->get();
                $city = json_decode(json_encode($city), true);
                for($k=0;$k<count($city);$k++)
                {
                    $city_id_here=$city[$k]['id'];
                    $region = DB::table('regions as region')->select(
                        'region.id',
                        'region.Name',
                        'region.city_id',
                        'city.Name as city_name'
                    )->where([['region.deleted_at',NULL],['region.city_id',$city_id_here]])
                        ->leftJoin('cities as city','region.city_id','=','city.id')->get();
                    $region = json_decode(json_encode($region),true);
                    $city[$k]['region']=$region;
                }
                $state[$j]['cities']=$city;
            }
            $country[$i]['states']=$state;
        }
        return $country;
    }
}
