<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IUnitRepositoryInterface;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\Unit\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitRepository implements IUnitRepositoryInterface
{
    public function all()
    {
        return UnitResource::collection(Unit::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return UnitResource::Collection(Unit::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $unit = new Unit();
        $unit->Name=$request->Name;
        $unit->company_id=$request->company_id;
        $unit->createdDate=date('Y-m-d h:i:s');
        $unit->isActive=1;
        $unit->user_id = $userId ?? 0;
        $unit->save();
        return new UnitResource(Unit::find($unit->id));
    }

    public function update(UnitRequest $unitRequest, $Id)
    {
        $userId = Auth::id();
        $unit = Unit::find($Id);
        $unitRequest['user_id']=$userId ?? 0;
        $unit->update($unitRequest->all());
        return new UnitResource(Unit::find($Id));
    }

    public function getById($Id)
    {
        return new UnitResource(Unit::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Unit::find($Id);
        $update->user_id=$userId;
        $update->save();
        $unit = Unit::withoutTrashed()->find($Id);
        if($unit->trashed())
        {
            return new UnitResource(Unit::onlyTrashed()->find($Id));
        }
        else
        {
            $unit->delete();
            return new UnitResource(Unit::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $unit = Unit::onlyTrashed()->find($Id);
        if (!is_null($unit))
        {
            $unit->restore();
            return new UnitResource(Unit::find($Id));
        }
        return new UnitResource(Unit::find($Id));
    }

    public function trashed()
    {
        $unit = Unit::onlyTrashed()->get();
        return UnitResource::collection($unit);
    }
}
