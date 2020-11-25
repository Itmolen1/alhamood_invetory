<?php
/**
 * Created by PhpStorm.
 * User: rizwanafridi
 * Date: 11/25/20
 * Time: 11:10
 */

namespace App\WebRepositories;


use App\Http\Requests\MeterReaderRequest;
use App\Models\MeterReader;
use App\Models\MeterReading;
use App\Models\Sale;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use Illuminate\Http\Request;

class MeterReadingRepository implements IMeterReadingRepositoryInterface
{


    public function index()
    {
        // TODO: Implement index() method.
        $meter_readings = MeterReading::with('meter_reading_details')->get();
        //dd($meter_readings);
        return view('admin.meterReading.index',compact('meter_readings'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $salesByDate['totalSale'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->sum('grandTotal');
        $salesByDate['firstPad'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->first()->sale_details->first()->PadNumber;
        $salesByDate['lastPad'] = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get()->last()->sale_details->last()->PadNumber;

        $meter_readers = MeterReader::all();
        return view('admin.meterReading.create',compact('salesByDate','meter_readers'));
    }

    public function store(Request $request)
    {
        // TODO: Implement store() method.
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
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