<?php


namespace App\WebRepositories;


use App\Http\Requests\RegionRequest;
use App\Models\City;
use App\Models\Region;
use App\WebRepositories\Interfaces\IRegionRepositoryInterface;
use Illuminate\Http\Request;

class RegionRepository implements IRegionRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $regions = Region::with('user','city')->get();
        //dd($states[0]->country->id);
        return view('admin.region.index',compact('regions'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $cities = City::all();
        return view('admin.region.create',compact('cities'));
    }

    public function store(RegionRequest $regionRequest)
    {
        //dd($regionRequest->all());
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $region = [
            'Name' =>$regionRequest->Name,
            'city_id' =>$regionRequest->city_id,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        Region::create($region);
        return redirect()->route('regions.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $region = Region::find($Id);
        $user_id = session('user_id');
        $region->update([
            'Name' =>$request->Name,
            'city_id' =>$request->city_id,
            'user_id' =>$user_id,
        ]);
        return redirect()->route('regions.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $cities = City::all();
        $region = Region::find($Id);
        return view('admin.region.edit',compact('region','cities'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Region::findOrFail($Id);
        $data->delete();
        return redirect()->route('regions.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function locationDetails($id)
    {
        // TODO: Implement locationDetails() method.
        $regions = Region::with('city.state.country'  )->find($id);
        return response()->json($regions);
    }
}
