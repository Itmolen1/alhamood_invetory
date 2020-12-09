<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Carbon\Traits\Date;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * @var ISaleRepositoryInterface
     */
    private $saleRepository;

    public function __construct(ISaleRepositoryInterface $saleRepository)
   {
       $this->saleRepository = $saleRepository;
   }

    public function index()
    {
        return $this->saleRepository->index();
    }


    public function create()
    {
        return $this->saleRepository->create();
    }


    public function store(Request $request)
    {
        $this->saleRepository->store($request);
    }


    public function show(Sale $sale)
    {
        //
    }


    public function edit($Id)
    {
        return $this->saleRepository->edit($Id);
    }



    public function salesUpdate(Request $request, $Id)
    {
        return $this->saleRepository->update($request, $Id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        //
    }

    public function customerSaleDetails($Id)
    {
        $sales = Sale::with('customer.vehicles','sale_details')
                       ->where([
                               'customer_id'=>$Id,
                               'IsPaid'=> false,
                           ])->get();
        return response()->json($sales);
    }

    public function salesByDateDetails($id)
    {
        // $customers = Customer::with('vehicles')->find($id);


        $salesData = Sale::with('sale_details')->where('SaleDate', $id)->get();
        if ($salesData != null)
        {

            $salesByDate['total'] = 0;
            foreach ($salesData as $data){
                $salesByDate['total'] += $data->sale_details[0]->Quantity;
            }
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            $salesByDate['firstPad'] = $salesData->first()->first()->sale_details->first()->PadNumber;
            $salesByDate['lastPad'] = $salesData->last()->sale_details->last()->PadNumber;
        }
        else
        {
            $salesByDate['sale_details'] = 0;
            $salesByDate['firstPad'] = 0;
            $salesByDate['lastPad'] = 0;
        }

//        $salesByDate['totalSale'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->sum('grandTotal');
//        $salesByDate['firstPad'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->first()->sale_details->first()->PadNumber;
//        $salesByDate['lastPad'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->last()->sale_details->last()->PadNumber;
//        $salesByDate1['firstPadSale'] = Sale::with('sale_details')->where('SaleDate', $id)->first();
//        $salesByDate1['firstPadSale2'] = $salesByDate1['firstPadSale']->sale_details1->first();
//        $salesByDate1['firstPad'] = $salesByDate1['firstPadSale2']->PadNumber;

        //$salesByDate['lastPadSale'] = $salesByDate->last();
//        $salesByDate['lastPadSaleDetail'] = $salesByDate->last()->sale_details->last();
//        $salesByDate['lastPad'] = $salesByDate->last()->sale_details->last()->PadNumber;
//        $salesByDate['firstPad'] = $salesByDate->first()->sale_details->first()->PadNumber;
        //$salesByDate['sumOfSale'] = $salesByDate->sum('grandTotal');
        return response()->json($salesByDate);
    }
}
