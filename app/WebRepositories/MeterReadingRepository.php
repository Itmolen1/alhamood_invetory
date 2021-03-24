<?php

namespace App\WebRepositories;

use App\Http\Requests\MeterReaderRequest;
use App\Models\MeterReader;
use App\Models\MeterReading;
use App\Models\MeterReadingDetail;
use App\Models\Sale;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use Illuminate\Http\Request;

class MeterReadingRepository implements IMeterReadingRepositoryInterface
{
    public function index()
    {
        $meter_readings = MeterReading::with('meter_reading_details')->where('company_id',session('company_id'))->get();
        return view('admin.meterReading.index',compact('meter_readings'));
    }

    public function create()
    {
//        $salesByDate['totalSale'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->sum('grandTotal');
//        $salesByDate['firstPad'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->first()->sale_details->first()->PadNumber;
//        $salesByDate['lastPad'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->last()->sale_details->last()->PadNumber;

        $salesData = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get();
        $total = 0;
        if ($salesData->first() != null)
        {
            foreach ($salesData as $data){
                $total += $data->sale_details[0]->Quantity;
             }
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            $salesByDate['firstPad'] = $salesData->first()->sale_details->first()->PadNumber;
            $salesByDate['lastPad'] = $salesData->last()->sale_details->last()->PadNumber;
        }
        else
        {
            $salesByDate['sale_details'] = 0;
            $salesByDate['firstPad'] = 0;
            $salesByDate['lastPad'] = 0;
        }

        $meter_readers = MeterReader::all();
        return view('admin.meterReading.create',compact('salesByDate','meter_readers','total'));
    }

    public function store(Request $request)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0) {
            //return Response()->json($request);
            // return Response()->json($request->Data['orders']);
            //return Response()->json($request->Data['PurchaseNumber']);
            //return Response()->json($request->Data['referenceNumber']);
            $user_id = session('user_id');
            $company_id = session('company_id');
            $reading = new MeterReading();
            $reading->readingDate = $request->Data['meterReadingDate'];
            $reading->startPad = $request->Data['startPad'];
            $reading->endPad = $request->Data['endPad'];
            $reading->totalMeterSale = $request->Data['totalSale'];
            $reading->totalPadSale = $request->Data['totalPad'];
            $reading->saleDifference = $request->Data['balance'];
            $reading->user_id = $user_id;
            $reading->company_id = $company_id;
            $reading->save();
            $reading = $reading->id;
            //return Response()->json($purchase);
            //$user = $sale->user_id;
            // return $sale;
            foreach($request->Data['orders'] as $detail)
            {
                //return $detail['Quantity'];
                //return Response()->json($detail['Quantity']);
                $data =  MeterReadingDetail::create([
                    "meter_reader_id"        => $detail['meter_id'],
                    "startReading"        => $detail['startReading'],
                    "endReading"        => $detail['endReading'],
                    "netReading"        => $detail['netReading'],
                    "Purchases"        => $detail['purchases'],
                    "Sales"        => $detail['sales'],
                    "Description"        => $detail['Description'],
                    "meter_reading_id"        => $reading,
                    "user_id" => $user_id,
                    "company_id" => $company_id,
                ]);
            }
            if ($data)
            {
                return Response()->json($data);
            }
        }
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $meterd = MeterReading::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            $meterd->update(
                [
                    'readingDate' => $request->Data['meterReadingDate'],
                    'startPad' => $request->Data['startPad'],
                    'endPad' => $request->Data['endPad'],
                    'totalMeterSale' => $request->Data['totalSale'],
                    'totalPadSale' => $request->Data['totalPad'],
                    'saleDifference' => $request->Data['balance'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'meter_readings';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->Data['UpdateDescription'];
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

            $d = MeterReadingDetail::where('meter_reading_id', array($Id))->delete();
            $slct = MeterReadingDetail::where('meter_reading_id', $Id)->get();
            foreach ($request->Data['orders'] as $detail)
            {
                $Details = MeterReadingDetail::create([
                    //"Id" => $detail['Id'],
                    "meter_reader_id"        => $detail['meter_id'],
                    "startReading"        => $detail['startReading'],
                    "endReading"        => $detail['endReading'],
                    "netReading"        => $detail['netReading'],
                    "Purchases"        => $detail['purchases'],
                    "Sales"        => $detail['sales'],
                    "Description"        => $detail['Description'],
                    "meter_reading_id"        => $Id,
                    "user_id" => $user_id,
                    "company_id" => $company_id,
                ]);
            }
            $ss = MeterReadingDetail::where('meter_reading_id', array($Details['meter_reading_id']))->get();
            return Response()->json($ss);
        }
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'meter_readings'])->get();
        $meter_readers = MeterReader::all();
        $meter_details = MeterReadingDetail::withTrashed()->with('meter_reading','user','meter_reader')->where('meter_reading_id', $Id)->get();
        return view('admin.meterReading.edit',compact('meter_details','meter_readers','update_notes'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
