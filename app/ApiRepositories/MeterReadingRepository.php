<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IMeterReadingRepositoryInterface;
use App\Http\Requests\MeterReadingRequest;
use App\Http\Resources\MeterReading\MeterReadingResource;
use App\Models\MeterReader;
use App\Models\MeterReading;
use App\Models\MeterReadingDetail;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeterReadingRepository implements IMeterReadingRepositoryInterface
{

    public function all()
    {
        return MeterReadingResource::collection(MeterReading::with('user','meter_reading_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return MeterReadingResource::Collection(MeterReading::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function ActivateDeactivate($Id)
    {
        $meter_reading = MeterReading::find($Id);
        if($meter_reading->isActive==1)
        {
            $meter_reading->isActive=0;
        }
        else
        {
            $meter_reading->isActive=1;
        }
        $meter_reading->update();
        return new MeterReadingResource(MeterReading::find($Id));
    }

    public function insert(Request $request)
    {
        $meter_reading_details=$request->meter_reading_details;

        $userId = Auth::id();
        $meter_reading = new MeterReading();
        $meter_reading->readingDate=$request->readingDate;
        $meter_reading->startPad=$request->startPad;
        $meter_reading->endPad=$request->endPad;
        $meter_reading->totalPadSale=$request->totalPadSale;
        $meter_reading->totalMeterSale=$request->totalMeterSale;
        $meter_reading->saleDifference=$request->saleDifference;
        $meter_reading->employee_id=$request->employee_id;
        $meter_reading->Description=$request->Description;
        $meter_reading->createdDate=date('Y-m-d h:i:s');
        $meter_reading->isActive=1;
        $meter_reading->user_id = $userId ?? 0;
        $meter_reading->company_id=Str::getCompany($userId);
        $meter_reading->save();
        $meter_reading_id = $meter_reading->id;

        foreach ($meter_reading_details as $meter_reading_item)
        {
            $data=MeterReadingDetail::create([
                'startReading'=>$meter_reading_item['startReading'],
                'endReading'=>$meter_reading_item['endReading'],
                'netReading'=>$meter_reading_item['netReading'],
                'Purchases'=>$meter_reading_item['Purchases'],
                'Sales'=>$meter_reading_item['Sales'],
                'meter_reading_id'=>$meter_reading_id,
                'meter_reader_id'=>$meter_reading_item['meter_reader_id'],
                'Description'=>$meter_reading_item['Description'],
                'meterDate'=>$meter_reading_item['meterDate'],
            ]);
        }
        $Response = MeterReadingResource::collection(MeterReading::where('id',$meter_reading->id)->with('user','meter_reading_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
        //return new PurchaseResource(Purchase::find($purchase->id));
    }

    public function update(MeterReadingRequest $meterReadingRequest, $Id)
    {
        $userId = Auth::id();
        $purchaseRequest['user_id']=$userId ?? 0;

        $meter_reading_details=$meterReadingRequest->meter_reading_details;

        $meter_reading = MeterReading::findOrFail($Id);
        $meter_reading->readingDate=$meterReadingRequest->readingDate;
        $meter_reading->startPad=$meterReadingRequest->startPad;
        $meter_reading->endPad=$meterReadingRequest->endPad;
        $meter_reading->totalPadSale=$meterReadingRequest->totalPadSale;
        $meter_reading->totalMeterSale=$meterReadingRequest->totalMeterSale;
        $meter_reading->saleDifference=$meterReadingRequest->saleDifference;
        $meter_reading->employee_id=$meterReadingRequest->employee_id;
        $meter_reading->Description=$meterReadingRequest->Description;
        $meter_reading->update();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'meter_readings';
        $update_note->RelationId = $Id;
        $update_note->Description = $meterReadingRequest->update_note;
        $update_note->user_id = $userId;
        $update_note->save();

        DB::table('meter_reading_details')->where([['meter_reading_id', $Id]])->delete();

        if(!empty($meter_reading_details))
        {
            foreach ($meter_reading_details as $meter_reading_item)
            {
                $data=MeterReadingDetail::create([
                    'startReading'=>$meter_reading_item['startReading'],
                    'endReading'=>$meter_reading_item['endReading'],
                    'netReading'=>$meter_reading_item['netReading'],
                    'Purchases'=>$meter_reading_item['Purchases'],
                    'Sales'=>$meter_reading_item['Sales'],
                    'meter_reading_id'=>$Id,
                    'meter_reader_id'=>$meter_reading_item['meter_reader_id'],
                    'Description'=>$meter_reading_item['Description'],
                    'meterDate'=>$meter_reading_item['meterDate'],
                ]);
            }
        }
        $Response = MeterReadingResource::collection(MeterReading::where('id',$Id)->with('user','meter_reading_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = MeterReadingResource::collection(MeterReading::where('id',$Id)->with('user','meter_reading_details','update_notes')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        return array('meters'=>MeterReader::select('id','Name')->orderBy('id','desc')->get());
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = MeterReading::find($Id);
        $update->user_id=$userId;
        $update->save();
        $meter_reading = MeterReading::withoutTrashed()->find($Id);
        if($meter_reading->trashed())
        {
            return new MeterReadingResource(MeterReading::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('meter_reading_details')->where([['meter_reading_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            $meter_reading->delete();
            return new MeterReadingResource(MeterReading::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier = MeterReading::onlyTrashed()->find($Id);
        if (!is_null($supplier))
        {
            $supplier->restore();
            return new MeterReadingResource(MeterReading::find($Id));
        }
        return new MeterReadingResource(MeterReading::find($Id));
    }

    public function trashed()
    {
        $supplier = MeterReading::onlyTrashed()->get();
        return MeterReadingResource::collection($supplier);
    }
}
