<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\Http\Requests\MeterReaderRequest;
use App\Http\Resources\MeterReader\MeterReaderResource;
use App\Models\MeterReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MeterReaderRepository implements IMeterReaderRepositoryInterface
{

    public function all()
    {
        return MeterReaderResource::collection(MeterReader::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return MeterReaderResource::Collection(MeterReader::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $meter = new MeterReader();
        $meter->Name=$request->Name;
        $meter->shortDescriptionForm=$request->shortDescriptionForm;
        $meter->company_id=$request->company_id;
        $meter->createdDate=date('Y-m-d h:i:s');
        $meter->isActive=1;
        $meter->user_id = $userId ?? 0;
        $meter->company_id=Str::getCompany($userId);
        $meter->save();
        return new MeterReaderResource(MeterReader::find($meter->id));
    }

    public function update(MeterReaderRequest $meterReaderRequest, $Id)
    {
        $userId = Auth::id();
        $meter = MeterReader::find($Id);
        $meterReaderRequest['user_id']=$userId ?? 0;
        $meter->update($meterReaderRequest->all());
        return new MeterReaderResource(MeterReader::find($Id));
    }

    public function getById($Id)
    {
        return new MeterReaderResource(MeterReader::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = MeterReader::find($Id);
        $update->user_id=$userId;
        $update->save();
        $meter = MeterReader::withoutTrashed()->find($Id);
        if($meter->trashed())
        {
            return new MeterReaderResource(MeterReader::onlyTrashed()->find($Id));
        }
        else
        {
            $meter->delete();
            return new MeterReaderResource(MeterReader::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $meter = MeterReader::onlyTrashed()->find($Id);
        if (!is_null($meter))
        {
            $meter->restore();
            return new MeterReaderResource(MeterReader::find($Id));
        }
        return new MeterReaderResource(MeterReader::find($Id));
    }

    public function trashed()
    {
        $meter = MeterReader::onlyTrashed()->get();
        return MeterReaderResource::collection($meter);
    }

    public function ActivateDeactivate($Id)
    {
        $meter = MeterReader::find($Id);
        if($meter->isActive==1)
        {
            $meter->isActive=0;
        }
        else
        {
            $meter->isActive=1;
        }
        $meter->update();
        return new MeterReaderResource(MeterReader::find($Id));
    }
}
