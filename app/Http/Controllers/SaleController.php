<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Carbon\Traits\Date;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    private $saleRepository;

    public function __construct(ISaleRepositoryInterface $saleRepository)
    {
       $this->saleRepository = $saleRepository;
    }

    public function CheckPadExist(Request $request)
    {
        return $this->saleRepository->CheckPadExist($request);
    }

    public function index()
    {
        return $this->saleRepository->index();
    }

    public function get_today_sale()
    {
        return $this->saleRepository->get_today_sale();
    }

    public function get_sale_of_date()
    {
        return $this->saleRepository->get_sale_of_date();
    }

    public function view_sale_of_date(Request $request)
    {
        return $this->saleRepository->view_sale_of_date($request);
    }

    public function view_result_sale_of_date(Request $request)
    {
        return $this->saleRepository->view_result_sale_of_date($request);
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

    public function all_sales(Request $request)
    {
        return $this->saleRepository->all_sales($request);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->saleRepository->delete($request, $Id);
    }

    public function get_data(Request $request)
    {
        return $this->saleRepository->get_data($request);
    }

    public function customerSaleDetails($Id)
    {
        $sales = Sale::with('customer','sale_details.vehicle')
                       ->where([
                               'customer_id'=>$Id,
                               'IsPaid'=> false,
                           ])->get();
        return response()->json($sales);
    }

    public function salesByDateDetails($id)
    {
        // $customers = Customer::with('vehicles')->find($id);
        $salesData = Sale::with('sale_details')->where('SaleDate', $id)->where('isActive','=',1)->get();
        if ($salesData != null)
        {
            $salesByDate['total'] = 0;
            $all_pads=array();
            foreach ($salesData as $data){
                $salesByDate['total'] += $data->sale_details[0]->Quantity;
                $all_pads[]=$data->sale_details[0]->PadNumber;
            }
            $filtered = array_diff($all_pads, array(null, 0));
            $max = max($filtered);
            $min = min($filtered);
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            $salesByDate['firstPad'] = $min;
            $salesByDate['lastPad'] = $max;
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
