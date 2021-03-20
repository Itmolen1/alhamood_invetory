<?php

namespace App\WebRepositories;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\Models\AccountTransaction;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\RequestEvent;

class   SaleRepository implements ISaleRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                    $button .= '<form action="'.route('sales.destroy', $data->id).'" method="POST">';
//                    $button .= @csrf_field();
//                    $button .= @method_field('DELETE');
//                    $button .= '&nbsp;&nbsp;';
//                    $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
//                    $button .= '</form>';
                    return $button;
                })
                 ->addColumn('createdDate', function($data) {
                        return $data->sale_details[0]->createdDate ?? "No date";
                    })
                 ->addColumn('PadNumber', function($data) {
                        return $data->sale_details[0]->PadNumber ?? "No Pad";
                    })
                 ->addColumn('customer', function($data) {
                        return $data->customer->Name ?? "No Name";
                    })
                 ->addColumn('registrationNumber', function($data) {
                        return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                    })
//                 ->addColumn('Product', function($data) {
//                        return $data->sale_details[0]->product->Name ?? "No product";
//                    })
                  ->addColumn('Quantity', function($data) {
                        return $data->sale_details[0]->Quantity ?? "No Quantity";
                    })
                   ->addColumn('Price', function($data) {
                        return $data->sale_details[0]->Price ?? "No Quantity";
                    })
                ->rawColumns(
                    [
                    'action',
                    // 'isActive',
                    'createdDate',
                    'PadNumber',
                    'customer',
                    'registrationNumber',
                    'Quantity',
                    'Price'
                    ])
                ->make(true);
        }
        return view('admin.sale.index');
    }

    public function get_today_sale()
    {
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    return $button;
                })
                ->addColumn('createdDate', function($data) {
                    return $data->sale_details[0]->createdDate ?? "No date";
                })
                ->addColumn('PadNumber', function($data) {
                    return $data->sale_details[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('registrationNumber', function($data) {
                    return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                })
                ->addColumn('Quantity', function($data) {
                    return $data->sale_details[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function($data) {
                    return $data->sale_details[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        'createdDate',
                        'PadNumber',
                        'customer',
                        'registrationNumber',
                        'Quantity',
                        'Price'
                    ])
                ->make(true);
        }
        return view('admin.sale.daily_sales');
    }

    public function get_sale_of_date()
    {
        return view('admin.sale.get_sales_of_date');
    }

    public function view_sale_of_date(Request $request)
    {
        $fromDate=$request->fromDate;
        return view('admin.sale.view_sales_of_date',compact('fromDate'));
    }

    public function view_result_sale_of_date(Request $request)
    {
        if(request()->ajax()) {
            return datatables()->of(Sale::with('sale_details.product', 'sale_details.vehicle', 'customer')->where('company_id', session('company_id'))->where('isActive', 1)->where('SaleDate', $_GET['fromDate'])->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="' . route('sales.edit', $data->id) . '"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    return $button;
                })
                ->addColumn('createdDate', function ($data) {
                    return $data->sale_details[0]->createdDate ?? "No date";
                })
                ->addColumn('PadNumber', function ($data) {
                    return $data->sale_details[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('customer', function ($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('registrationNumber', function ($data) {
                    return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                })
                ->addColumn('Quantity', function ($data) {
                    return $data->sale_details[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function ($data) {
                    return $data->sale_details[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        'createdDate',
                        'PadNumber',
                        'customer',
                        'registrationNumber',
                        'Quantity',
                        'Price'
                    ])
                ->make(true);
        }
    }

    public function all_sales(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );

//        $sql_count_without_filter = 'select s.*, c.Name from sales as s left join customers as c on c.id = s.customer_id join (SELECT * FROM sale_details WHERE deleted_at is null) as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 ';
//        $sales_count_without_filter = DB::select( DB::raw($sql_count_without_filter));
//        $totalData =$sales_count_without_filter;
        $totalData = Sale::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
//            $sales = Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->where('isActive',1)->offset($start)
//                ->limit($limit)
//                ->orderBy($order,$dir)
//                ->get();

            $sql = 'select s.id,s.company_id,s.customer_id,s.customer_id,s.SaleDate,s.totalVat,s.grandTotal,s.paidBalance, c.Name, sd.PadNumber,sd.Quantity,sd.Price,sd.registrationNumber from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.*,v.registrationNumber FROM sale_details join vehicles as v on sale_details.vehicle_id=v.id WHERE sale_details.deleted_at is null) as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1  order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));
            //echo "<pre>";print_r($sales);die;
        }
        else {
            $search = $request->input('search.value');

//            $sales =  Sale::with('sale_details.product','sale_details.vehicle',)->where('company_id',session('company_id'))->where('isActive',1)->where('id','LIKE',"%{$search}%")
//                ->orWhere('customer_id', 'LIKE',"%{$search}%")
//                ->offset($start)
//                ->limit($limit)
//                ->orderBy($order,$dir)
//                ->get();

//            DB::enableQueryLog();
//            $sales= DB::table('sales as s')->select('s.id','s.customer_id','s.SaleDate','s.totalVat','s.grandTotal','s.paidBalance','s.remainingBalance','s.isActive','s.totalVat','s.totalVat','s.totalVat','s.company_id','c.Name','sale_details.PadNumber','sale_details.deleted_at','sale_details.id as detail_id')->leftjoin('customers as c', 'c.id', '=', 's.customer_id')->leftJoin('sale_details', 's.id', '=', 'sale_details.sale_id')->where('s.company_id',session('company_id'))->where('s.isActive',1)->where('sale_details.deleted_at',null)->where('c.Name','LIKE',"%{$search}%")->orWhere('sale_details.PadNumber','LIKE',"%{$search}%")->offset($start)
//                ->limit($limit)->orderBy($order,$dir)->get();
//            $query = DB::getQueryLog();
//            print_r($query);
//            echo "<pre>";print_r($sales);die;

            //DB::enableQueryLog();

//            $sales= DB::table('sales as s')
//                    ->select('s.*','c.Name',)
//                    ->leftjoin('customers as c', 'c.id', '=', 's.customer_id')
//                    ->join(DB::raw('(SELECT * FROM sale_details WHERE `deleted_at` is null AND PadNumber LIKE "%'.$search.'%")'),function($join)
//                    {
//                        $join->on('s.id', '=', 'sale_details.sale_id');
//                    })
//                    ->where('s.company_id',session('company_id'))
//                    ->where('s.isActive',1)
//                ->where('c.Name','LIKE',"%{$search}%")
//                ->offset($start)
//                ->limit($limit)
//                ->orderBy($order,$dir)
//                ->get();

            $sql = 'select s.*, c.Name,sd.PadNumber,sd.Quantity,sd.Price,sd.registrationNumber from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.*,v.registrationNumber FROM sale_details join vehicles as v on sale_details.vehicle_id=v.id WHERE sale_details.deleted_at is null and sale_details.PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));

            //$query = DB::getQueryLog();
            //print_r($query);

            //echo "<pre>";print_r($sales);die;

            $sql_count = 'select COUNT(*) TotalCount,s.*, c.Name from sales as s left join customers as c on c.id = s.customer_id join (SELECT * FROM sale_details WHERE deleted_at is null and PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            //echo "<pre>";print_r($sales_count);die;
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }


//            $totalFiltered = Sale::where('id','LIKE',"%{$search}%")
//                ->orWhere('customer_id', 'LIKE',"%{$search}%")
//                ->count();
        }

        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $sale)
            {
                $edit =  route('sales.edit',$sale->id);

                $nestedData['id'] = $sale->id;
                $nestedData['SaleDate'] = $sale->SaleDate;
                $nestedData['PadNumber'] = $sale->PadNumber ?? "No Pad";
                $nestedData['customer'] = $sale->Name ?? "No Name";
                $nestedData['registrationNumber'] = $sale->registrationNumber ?? "No Number";
                $nestedData['Quantity'] = $sale->Quantity ?? 0.00;
                $nestedData['Price'] = $sale->Price ?? 0.00;
                $nestedData['totalVat'] = $sale->totalVat ?? 0.00;
                $nestedData['grandTotal'] = $sale->grandTotal ?? 0.00;
                $nestedData['paidBalance'] = $sale->paidBalance ?? 0.00;
                $nestedData['action'] = '<a href="'.route('sales.edit', $sale->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    public function create()
    {
        $saleNo = $this->invoiceNumber();
        $init_data = $this->PadNumber();
        $customers = Customer::with('customer_prices')->where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $salesRecords = Sale::with('sale_details.vehicle','customer')->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
        return view('admin.sale.create',compact('customers','saleNo','products','salesRecords','init_data'));
    }

    public function store(Request $request)
    {
        if(isset($request->Data['orders'][0]['PadNumber']))
        {
            $pad_number=$request->Data['orders'][0]['PadNumber'];
        }
        else
        {
            $pad_number=0;
        }

        //check pad number already exist or not
        if($pad_number!=0)
        {
            $already_exist = SaleDetail::where('company_id',session('company_id'))->where('PadNumber',$pad_number)->get();
            if(!$already_exist->isEmpty())
            {
                $data=array('result'=>false,'message'=>'PAD NUMBER ALREADY EXIST');
                echo json_encode($data);exit();
            }
        }

        //check cash paid is not grater than grand total
        if($request->Data['paidBalance'] > $request->Data['grandTotal'])
        {
            $data=array('result'=>false,'message'=>'CAN NOT ENTER EXTRA CASH HERE GO TO ADVANCES');
            echo json_encode($data);exit();
        }

        $AllRequestCount = collect($request->Data)->count();

        DB::transaction(function () use($AllRequestCount,$request){
            if($AllRequestCount > 0)
            {
                if($request->Data['remainingBalance']<0)
                {
                    if($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                    {
                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                            $isPaid_current = false;
                            $partialPaid_current =false;
                        }
                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                        {
                            $isPaid_current = 1;
                            $partialPaid_current=0;
                            // if the paidBalance = cashPaid is more than grand total we need to divide extra
                            // amount to unpaid sales if its there any entry
                            $all_sales = Sale::with('customer','sale_details')->where([
                                'customer_id'=>$request->Data['customer_id'],
                                'IsPaid'=> false,
                            ])->orderBy('SaleDate')->get();
                            //dd($all_purchase);
                            $total_i_have=$request->Data['paidBalance']-$request->Data['grandTotal'];

                            foreach($all_sales as $sale)
                            {
                                $total_you_need = $sale->remainingBalance;
                                $still_payable_to_you=0;
                                $total_giving_to_you=0;
                                $isPartialPaid = false;
                                if ($total_i_have >= $total_you_need)
                                {
                                    $isPaid = true;
                                    $isPartialPaid = false;
                                    $total_i_have -= $total_you_need;
                                    $total_giving_to_you=$total_you_need;
                                }
                                elseif($total_i_have <= $total_you_need){
                                    $isPaid = false;
                                    $isPartialPaid = true;
                                    $total_giving_to_you=$total_i_have;
                                    $still_payable_to_you=$total_you_need-$total_i_have;
                                    $total_i_have -= $total_giving_to_you;
                                }

                                if($isPartialPaid==true)
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->paidBalance+$total_giving_to_you,
                                        "remainingBalance"   => $sale->remainingBalance-$total_giving_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }
                                else
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->grandTotal,
                                        "remainingBalance"   => $still_payable_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }

                                if($total_i_have<=0)
                                {
                                    break;
                                }
                            }
                        }
                        else
                        {
                            $isPaid_current = false;
                            $partialPaid_current =true;
                        }

                        $user_id = session('user_id');
                        $company_id = session('company_id');

                        $sale = new Sale();
                        $sale->SaleNumber = $request->Data['SaleNumber'];
                        $sale->SaleDate = $request->Data['SaleDate'];
                        $sale->Total = $request->Data['Total'];
                        $sale->subTotal = $request->Data['subTotal'];
                        $sale->totalVat = $request->Data['totalVat'];
                        $sale->grandTotal = $request->Data['grandTotal'];
                        if($request->Data['lastClosing']<0 && $request->Data['paidBalance']==0 || $request->Data['paidBalance']==0.00)
                        {
                            $sale->paidBalance = ($request->Data['grandTotal']-$request->Data['paidBalance']-$request->Data['remainingBalance']);
                            $sale->remainingBalance = $request->Data['remainingBalance'];
                            $isPaid_current = false;
                            $partialPaid_current =true;
                        }
                        elseif($request->Data['lastClosing']<0)
                        {
                            $sale->paidBalance = ($request->Data['grandTotal']-$request->Data['paidBalance']-$request->Data['lastClosing']);
                            $sale->remainingBalance = $request->Data['remainingBalance'];
                        }
                        else
                        {
                            $sale->paidBalance = $request->Data['grandTotal'];
                            $sale->remainingBalance = 0;
                        }
                        //$sale->paidBalance = $request->Data['grandTotal'];
                        //$sale->remainingBalance = 0;
                        $sale->customer_id = $request->Data['customer_id'];
                        $sale->Description = 'AutoPaid';
                        $sale->IsPaid = $isPaid_current;
                        $sale->IsPartialPaid = $partialPaid_current;
                        $sale->IsReturn = false;
                        $sale->IsPartialReturn = false;
                        $sale->IsNeedStampOrSignature = false;
                        $sale->user_id = $user_id;
                        $sale->company_id = $company_id;
                        $sale->save();
                        $sale = $sale->id;
                        foreach($request->Data['orders'] as $detail)
                        {
                            $data =  SaleDetail::create([
                                "product_id"        => $detail['product_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "unit_id"        => $detail['unit_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $sale,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                    }
                    else
                    {
                        $isPaid = true;
                        $partialPaid =false;

                        $user_id = session('user_id');
                        $company_id = session('company_id');

                        $sale = new Sale();
                        $sale->SaleNumber = $request->Data['SaleNumber'];
                        $sale->SaleDate = $request->Data['SaleDate'];
                        $sale->Total = $request->Data['Total'];
                        $sale->subTotal = $request->Data['subTotal'];
                        $sale->totalVat = $request->Data['totalVat'];
                        $sale->grandTotal = $request->Data['grandTotal'];
                        $sale->paidBalance = $request->Data['grandTotal'];
                        $sale->remainingBalance = 0;
                        $sale->customer_id = $request->Data['customer_id'];
                        $sale->Description = 'AutoPaid';
                        $sale->IsPaid = $isPaid;
                        $sale->IsPartialPaid = $partialPaid;
                        $sale->IsReturn = false;
                        $sale->IsPartialReturn = false;
                        $sale->IsNeedStampOrSignature = false;
                        $sale->user_id = $user_id;
                        $sale->company_id = $company_id;
                        $sale->save();
                        $sale = $sale->id;
                        foreach($request->Data['orders'] as $detail)
                        {
                            $data =  SaleDetail::create([
                                "product_id"        => $detail['product_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "unit_id"        => $detail['unit_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $sale,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                    }
                }
                else
                {
                    if($request->Data['remainingBalance']>0 && $request->Data['lastClosing']<0)
                    {
                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                            $isPaid_current = false;
                            $partialPaid_current =false;
                        }
                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                        {
                            $isPaid_current = 1;
                            $partialPaid_current=0;
                            // if the paidBalance = cashPaid is more than grand total we need to divide extra
                            // amount to unpaid sales if its there any entry
                            $all_sales = Sale::with('customer','sale_details')->where([
                                'customer_id'=>$request->Data['customer_id'],
                                'IsPaid'=> false,
                            ])->orderBy('SaleDate')->get();
                            //dd($all_purchase);
                            $total_i_have=$request->Data['paidBalance']-$request->Data['grandTotal'];

                            foreach($all_sales as $sale)
                            {
                                $total_you_need = $sale->remainingBalance;
                                $still_payable_to_you=0;
                                $total_giving_to_you=0;
                                $isPartialPaid = false;
                                if ($total_i_have >= $total_you_need)
                                {
                                    $isPaid = true;
                                    $isPartialPaid = false;
                                    $total_i_have -= $total_you_need;
                                    $total_giving_to_you=$total_you_need;
                                }
                                elseif($total_i_have <= $total_you_need){
                                    $isPaid = false;
                                    $isPartialPaid = true;
                                    $total_giving_to_you=$total_i_have;
                                    $still_payable_to_you=$total_you_need-$total_i_have;
                                    $total_i_have -= $total_giving_to_you;
                                }

                                if($isPartialPaid==true)
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->paidBalance+$total_giving_to_you,
                                        "remainingBalance"   => $sale->remainingBalance-$total_giving_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }
                                else
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->grandTotal,
                                        "remainingBalance"   => $still_payable_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }

                                if($total_i_have<=0)
                                {
                                    break;
                                }
                            }
                        }
                        else
                        {
                            $isPaid_current = false;
                            $partialPaid_current =true;
                        }
                        $user_id = session('user_id');
                        $company_id = session('company_id');

                        $sale = new Sale();
                        $sale->SaleNumber = $request->Data['SaleNumber'];
                        $sale->SaleDate = $request->Data['SaleDate'];
                        $sale->Total = $request->Data['Total'];
                        $sale->subTotal = $request->Data['subTotal'];
                        $sale->totalVat = $request->Data['totalVat'];
                        $sale->grandTotal = $request->Data['grandTotal'];
                        if($request->Data['lastClosing']<0 && $request->Data['paidBalance']==0 || $request->Data['paidBalance']==0.00)
                        {
                            $sale->paidBalance = ($request->Data['grandTotal']-$request->Data['paidBalance']-$request->Data['remainingBalance']);
                            $sale->remainingBalance = $request->Data['remainingBalance'];
                            $isPaid_current = false;
                            $partialPaid_current =true;
                        }
                        elseif($request->Data['lastClosing']<0 && $request->Data['paidBalance'] <= $request->Data['grandTotal'])
                        {
                            $sale->paidBalance = $request->Data['paidBalance']-$request->Data['lastClosing'];
                            $sale->remainingBalance = $request->Data['remainingBalance'];
                        }
                        else
                        {
                            $sale->paidBalance = $request->Data['grandTotal'];
                            $sale->remainingBalance = 0;
                        }
                        $sale->customer_id = $request->Data['customer_id'];
                        $sale->Description = $request->Data['customerNote'];
                        $sale->IsPaid = $isPaid_current;
                        $sale->IsPartialPaid = $partialPaid_current;
                        $sale->IsReturn = false;
                        $sale->IsPartialReturn = false;
                        $sale->IsNeedStampOrSignature = false;
                        $sale->user_id = $user_id;
                        $sale->company_id = $company_id;
                        $sale->save();
                        $sale = $sale->id;
                        foreach($request->Data['orders'] as $detail)
                        {
                            $data =  SaleDetail::create([
                                "product_id"        => $detail['product_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "unit_id"        => $detail['unit_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $sale,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                    }
                    else
                    {
                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                            $isPaid_current = false;
                            $partialPaid_current =false;
                        }
                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                        {
                            $isPaid_current = 1;
                            $partialPaid_current=0;
                            // if the paidBalance = cashPaid is more than grand total we need to divide extra
                            // amount to unpaid sales if its there any entry
                            $all_sales = Sale::with('customer','sale_details')->where([
                                'customer_id'=>$request->Data['customer_id'],
                                'IsPaid'=> false,
                            ])->orderBy('SaleDate')->get();
                            //dd($all_purchase);
                            $total_i_have=$request->Data['paidBalance']-$request->Data['grandTotal'];

                            foreach($all_sales as $sale)
                            {
                                $total_you_need = $sale->remainingBalance;
                                $still_payable_to_you=0;
                                $total_giving_to_you=0;
                                $isPartialPaid = false;
                                if ($total_i_have >= $total_you_need)
                                {
                                    $isPaid = true;
                                    $isPartialPaid = false;
                                    $total_i_have -= $total_you_need;
                                    $total_giving_to_you=$total_you_need;
                                }
                                elseif($total_i_have <= $total_you_need){
                                    $isPaid = false;
                                    $isPartialPaid = true;
                                    $total_giving_to_you=$total_i_have;
                                    $still_payable_to_you=$total_you_need-$total_i_have;
                                    $total_i_have -= $total_giving_to_you;
                                }

                                if($isPartialPaid==true)
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->paidBalance+$total_giving_to_you,
                                        "remainingBalance"   => $sale->remainingBalance-$total_giving_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }
                                else
                                {
                                    $this_sale = Sale::find($sale->id);
                                    $this_sale->update([
                                        "paidBalance"        => $sale->grandTotal,
                                        "remainingBalance"   => $still_payable_to_you,
                                        "IsPaid" => $isPaid,
                                        "IsPartialPaid" => $isPartialPaid,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                        "account_transaction_payment_id" => 'CashOverflow',
                                    ]);
                                }

                                if($total_i_have<=0)
                                {
                                    break;
                                }
                            }
                        }
                        else
                        {
                            $isPaid_current = false;
                            $partialPaid_current =true;
                        }
                        $user_id = session('user_id');
                        $company_id = session('company_id');

                        $sale = new Sale();
                        $sale->SaleNumber = $request->Data['SaleNumber'];
                        $sale->SaleDate = $request->Data['SaleDate'];
                        $sale->Total = $request->Data['Total'];
                        $sale->subTotal = $request->Data['subTotal'];
                        $sale->totalVat = $request->Data['totalVat'];
                        $sale->grandTotal = $request->Data['grandTotal'];
                        if($request->Data['lastClosing']<0)
                        {
                            $sale->paidBalance = ($request->Data['grandTotal']-$request->Data['paidBalance']-$request->Data['lastClosing']);
                            $sale->remainingBalance = $request->Data['remainingBalance'];
                        }
                        else
                        {
                            $sale->paidBalance = $request->Data['paidBalance'];
                            $sale->remainingBalance = $request->Data['grandTotal']-$request->Data['paidBalance'];
                        }
                        $sale->customer_id = $request->Data['customer_id'];
                        $sale->Description = $request->Data['customerNote'];
                        $sale->IsPaid = $isPaid_current;
                        $sale->IsPartialPaid = $partialPaid_current;
                        $sale->IsReturn = false;
                        $sale->IsPartialReturn = false;
                        $sale->IsNeedStampOrSignature = false;
                        $sale->user_id = $user_id;
                        $sale->company_id = $company_id;
                        $sale->save();
                        $sale = $sale->id;

                        foreach($request->Data['orders'] as $detail)
                        {
                            $pad_number=$detail['PadNumber'];
                            $data =  SaleDetail::create([
                                "product_id"        => $detail['product_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "unit_id"        => $detail['unit_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $sale,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                    }
                }

                if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$sale;
                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                    $cash_transaction->Type='sales';
                    $cash_transaction->Details='CashSales|'.$sale;
                    $cash_transaction->Credit=0.00;
                    $cash_transaction->Debit=$request->Data['paidBalance'];
                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $pad_number;
                    $cash_transaction->save();
                }

                ////////////////// start account section gautam ////////////////
                if($sale)
                {
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                    // totally credit
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$sale,
                                'referenceNumber'=>'P#'.$detail['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // partial payment some cash some credit
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $difference = $differenceValue + $request->Data['grandTotal'];

                        //make debit entry for the sales
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['grandTotal'],
                                'Differentiate' => $totalCredit,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$sale,
                                'referenceNumber'=>'P#'.$detail['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        //make credit entry for the whatever cash is paid
                        $difference=$totalCredit-$request->Data['paidBalance'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $request->Data['paidBalance'],
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$sale,
                                'referenceNumber'=>'P#'.$detail['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // fully paid with cash
                    else
                    {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => $request->Data['SaleDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sale,
                            'referenceNumber'=>'P#'.$detail['PadNumber'],
                        ]);

                        //make credit entry for the whatever cash is paid
                        $difference=$difference-$request->Data['paidBalance'];
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['paidBalance'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->Data['SaleDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSales|'.$sale,
                            'referenceNumber'=>'P#'.$detail['PadNumber'],
                        ]);
                    }
                    //return Response()->json($AccountTransactions);

                    $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
                    echo json_encode($data);
                }
                ////////////////// end account section gautam ////////////////
            }
        });
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use($AllRequestCount,$request,$Id){
            if($AllRequestCount > 0)
            {
                $sold = Sale::with('customer.account_transaction')->find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                ////////////////// account section gautam ////////////////
                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                if (!is_null($accountTransaction))
                {
                    // payment is done (in any way - advance or payment)
                    if($sold->IsPaid==1 && $sold->IsPartialPaid==0)
                    {
                        // if more cash incoming then need to add in customer account

                        //check if only customer is changed and not quantity or price = grand total is same as previous
                        if($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                        {
                            //supplier is changed need to reverse all previously made account entries for the previous supplier

                            // start reverse entry for wrong supplier
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong supplier

                            //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                            $description_string1='FullCashSales|'.$Id;
                            $description_string2='PartialCashSales|'.$Id;
                            $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                            if($previous_probable_cash_entry)
                            {
                                $previously_credited = $previous_probable_cash_entry->Credit;
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                if($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string1;
                                }
                                elseif($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string2;
                                }
                                else
                                {
                                    $new_desc_string='';
                                }
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>$new_desc_string,
                                        'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end

                                $description_string1='CashSales|'.$Id;
                                $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                                if($previous_probable_cash_entry)
                                {
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSalesReversal|'.$Id;
                                    $cash_transaction->Credit=$previously_credited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_credited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }

                                // if cash paid is same need to make new cash entry here
                            }
                            //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                            if($request->Data['paidBalance']!=0)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                                if($cashTransaction)
                                {
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }
                            }

                            /*new entry*/
                            // start new entry for right supplier and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are receivable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now customer is payable so
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $request->Data['grandTotal'],
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => '',
                                        'referenceNumber'=>$accountTransaction->last()->referenceNumber,
                                    ]);
                                }
                            }
                            /*new entry*/
                        }

                        // check if only grand total is changed and not the customer
                        elseif($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            // 1 : reverse older entry
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                            $description_string1='FullCashSales|'.$Id;
                            $description_string2='PartialCashSales|'.$Id;
                            $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                            $cash_flag=0;
                            if($previous_probable_cash_entry)
                            {
                                $previously_credited = $previous_probable_cash_entry->Credit;
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                if($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string1;
                                }
                                elseif($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string2;
                                }
                                else
                                {
                                    $new_desc_string='';
                                }
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>$new_desc_string,
                                        'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                $cash_flag=1;

                                $description_string1='CashSales|'.$Id;
                                $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                                if($previous_probable_cash_entry)
                                {
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSalesReversal|'.$Id;
                                    $cash_transaction->Credit=$previously_credited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_credited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }
                                // if cash paid is same need to make new cash entry here
                            }
                            //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                            /* new entry start */
                            // make new entry then check account balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate - $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            $accountTransaction_ref=$AccountTransactions->id;
                            /* new entry end */

                            if($request->Data['paidBalance']!=0)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                                if($cashTransaction)
                                {
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();

                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                    $last_closing = $accountTransaction->last()->Differentiate;
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => $request->Data['paidBalance'],
                                            'Debit' => 0.00,
                                            'Differentiate' => $last_closing+$request->Data['paidBalance'],
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'FullCashSales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                            }

                            // if difference is positive meaning advance is over and we are receivable
                            // so update sales entry with difference amount as received amount
                            if($cash_flag)
                            {
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid1',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($request->Data['paidBalance']==($request->Data['grandTotal']))
                                    {
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['grandTotal'],
                                            "remainingBalance"   => 0,
                                            "IsPaid" => 1,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'CP1',
                                        ]);
                                    }
                                    else
                                    {
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['paidBalance'],
                                            "remainingBalance"   => $request->Data['grandTotal']-$request->Data['paidBalance'],
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'CP2',
                                        ]);
                                    }
                                }
                            }
                            else
                            {
                                if($difference==$request->Data['grandTotal'])
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid2',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid3',
                                    ]);
                                }
                            }

                            // fully paid case will come here
                        }

                        // check both customer and grandTotal is changed meaning case 3
                        elseif($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            // start reverse entry for wrong customer with wrong entries
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'referenceNumber'=>$previous_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong customer

                            //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                            $description_string1='FullCashSales|'.$Id;
                            $description_string2='PartialCashSales|'.$Id;
                            $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                            if($previous_probable_cash_entry)
                            {
                                $previously_credited = $previous_probable_cash_entry->Credit;
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                if($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string1;
                                }
                                elseif($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string2;
                                }
                                else
                                {
                                    $new_desc_string='';
                                }
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>$new_desc_string,
                                        'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end

                                $description_string1='CashSales|'.$Id;
                                $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                                if($previous_probable_cash_entry)
                                {
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSalesReversal|'.$Id;
                                    $cash_transaction->Credit=$previously_credited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_credited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }

                                // if cash paid is same need to make new cash entry here
                            }
                            //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                            if($request->Data['paidBalance']!=0)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                                if($cashTransaction)
                                {
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }
                            }

                            /*new entry with right grand total */
                            // start new entry for right customer and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'referenceNumber'=>$accountTransaction->last()->referenceNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // check if new incoming entry is credit or not
                            if($request->Data['paidBalance']==0)
                            {
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $request->Data['grandTotal'],
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => '',
                                ]);
                            }
                            else
                            {
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($difference==($request->Data['grandTotal']))
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => 0,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                    else
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => 0,
                                            "remainingBalance"   => $request->Data['grandTotal'],
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                }
                            }

                            /*new entry with right grand total*/
                        }

                        // check only cash is reduction in payment nothing else
                        elseif ($request->Data['paidBalance']!=$sold->paidBalance)
                        {
                            // 1 : reverse older entry
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                            $description_string1='FullCashSales|'.$Id;
                            $description_string2='PartialCashSales|'.$Id;
                            $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                            $cash_flag=0;
                            if($previous_probable_cash_entry)
                            {
                                $previously_credited = $previous_probable_cash_entry->Credit;
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                if($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string1;
                                }
                                elseif($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string2;
                                }
                                else
                                {
                                    $new_desc_string='';
                                }
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>$new_desc_string,
                                        'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                $cash_flag=1;

                                $description_string1='CashSales|'.$Id;
                                $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                                if($previous_probable_cash_entry)
                                {
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSalesReversal|'.$Id;
                                    $cash_transaction->Credit=$previously_credited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_credited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }
                                // if cash paid is same need to make new cash entry here
                            }
                            //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                            /* new entry start */
                            // make new entry then check account balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate - $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            $accountTransaction_ref=$AccountTransactions->id;
                            /* new entry end */

                            if($request->Data['paidBalance']!=0)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                                if($cashTransaction)
                                {
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();

                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                    $last_closing = $accountTransaction->last()->Differentiate;
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => $request->Data['paidBalance'],
                                            'Debit' => 0.00,
                                            'Differentiate' => $last_closing+$request->Data['paidBalance'],
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'FullCashSales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                            }

                            // if difference is positive meaning advance is over and we are receivable
                            // so update sales entry with difference amount as received amount
                            if($cash_flag)
                            {
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid1',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($request->Data['paidBalance']==($request->Data['grandTotal']))
                                    {
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['grandTotal'],
                                            "remainingBalance"   => 0,
                                            "IsPaid" => 1,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'CP1',
                                        ]);
                                    }
                                    else
                                    {
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['paidBalance'],
                                            "remainingBalance"   => $request->Data['grandTotal']-$request->Data['paidBalance'],
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'CP2',
                                        ]);
                                    }
                                }
                            }
                            else
                            {
                                if($difference==$request->Data['grandTotal'])
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid2',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid3',
                                    ]);
                                }
                            }
                        }
                    }
                    elseif($sold->IsPaid==0 && $sold->IsPartialPaid==1)
                    {
                        // if more cash incoming then need to add in customer account

                        //check if only customer is changed and not quantity or price = grand total is same as previous
                        if($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                        {
                            //supplier is changed need to reverse all previously made account entries for the previous supplier

                            // start reverse entry for wrong supplier
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong supplier

                            /*new entry*/
                            // start new entry for right supplier and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are receivable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now we are receivable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                            /*new entry*/
                        }

                        // check if only grand total is changed and not the customer
                        elseif($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            // 1 : reverse older entry
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            /* new entry start */
                            // make new entry then check account balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();

                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            $accountTransaction_ref=$AccountTransactions->id;
                            /* new entry end */

                            // if difference is positive meaning advance is over and we are receivable
                            // so update sales entry with difference amount as received amount
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                            // fully paid case will come here
                        }

                        // check both customer and grandTotal is changed meaning case 3
                        elseif($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            // start reverse entry for wrong customer with wrong entries
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing-$previously_debited,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong customer

                            //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                            $description_string1='FullCashSales|'.$Id;
                            $description_string2='PartialCashSales|'.$Id;
                            $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                            if($previous_probable_cash_entry)
                            {
                                $previously_credited = $previous_probable_cash_entry->Credit;
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                if($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string1;
                                }
                                elseif($previous_probable_cash_entry->Description==$description_string1)
                                {
                                    $new_desc_string=$description_string2;
                                }
                                else
                                {
                                    $new_desc_string='';
                                }
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>$new_desc_string,
                                        'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end

                                $description_string1='CashSales|'.$Id;
                                $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                                if($previous_probable_cash_entry)
                                {
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSalesReversal|'.$Id;
                                    $cash_transaction->Credit=$previously_credited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_credited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                    $cash_transaction->save();
                                }

                                // if cash paid is same need to make new cash entry here
                            }
                            //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                            /*new entry with right grand total */
                            // start new entry for right customer and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            $totalDebit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalDebit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            if($request->Data['paidBalance']==0)
                            {
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $request->Data['grandTotal'],
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => '',
                                ]);
                            }
                            else
                            {
                                if ($difference < 0) {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance" => $request->Data['grandTotal'],
                                        "remainingBalance" => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                } elseif ($difference > 0) {
                                    if ($difference == ($request->Data['grandTotal'])) {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance" => 0,
                                            "remainingBalance" => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    } else {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance" => $request->Data['grandTotal'] - $difference,
                                            "remainingBalance" => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                }
                            }
                            /*new entry with right grand total*/
                        }

                        if($request->Data['paidBalance']!=$sold->paidBalance)
                        {
                            //hello
                            //meaning only paid cash is amount is changing
                        }
                    }
                    elseif($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        if($request->Data['lastClosing']<0)
                        {
                            // if account closing is less then zero meaning it has some advance amount
                            // so deduct that amount and make related entry

                            //check if only customer is changed and not quantity or price = grand total is same as previous
                            if($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                            {
                                //supplier is changed need to reverse all previously made account entries for the previous supplier

                                // start reverse entry for wrong supplier
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done for wrong supplier

                                /*new entry*/
                                // start new entry for right supplier and credit or debit account based on closing balance
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                $totalDebit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalDebit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($difference==($request->Data['grandTotal']))
                                    {
                                        // now we are receivable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => 0,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                    else
                                    {
                                        // now we are receivable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['grandTotal']-$difference,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                }
                                /*new entry*/
                            }

                            // check if only grand total is changed and not the customer
                            elseif($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                            {
                                // 1 : reverse older entry
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                /* new entry start */
                                // make new entry then check account balance
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();

                                $totalDebit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalDebit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                $accountTransaction_ref=$AccountTransactions->id;
                                /* new entry end */

                                // if difference is positive meaning advance is over and we are receivable
                                // so update sales entry with difference amount as received amount
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($difference==($request->Data['grandTotal']))
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => 0,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                    else
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['grandTotal']-$difference,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                }
                                // fully paid case will come here
                            }

                            // check both customer and grandTotal is changed meaning case 3
                            elseif($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                            {
                                // start reverse entry for wrong customer with wrong entries
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done for wrong customer

                                /*new entry with right grand total */
                                // start new entry for right customer and credit or debit account based on closing balance
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                $totalDebit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalDebit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                if($difference<0)
                                {
                                    // still there is advance amount so make it fully paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => 0.00,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                elseif($difference>0)
                                {
                                    if($difference==($request->Data['grandTotal']))
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => 0,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 0,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                    else
                                    {
                                        // now we are payable so differance amount will be paid amount and make it partial paid
                                        $this_sales = Sale::find($sold->id);
                                        $this_sales->update([
                                            "paidBalance"        => $request->Data['grandTotal']-$difference,
                                            "remainingBalance"   => $difference,
                                            "IsPaid" => 0,
                                            "IsPartialPaid" => 1,
                                            "IsNeedStampOrSignature" => false,
                                            "Description" => 'AutoPaid',
                                        ]);
                                    }
                                }
                                /*new entry with right grand total*/
                            }
                        }
                        else
                        {
                            // if paid balance is not same as earlier need to update cash account as well
                            if($sold->paidBalance!=$request->Data['paidBalance'])
                            {
                                //check if previously cash transaction done with this sales id
                                $description_string='CashSales|'.$Id;
                                $previous_cash_entry = CashTransaction::get()->where('company_id','=',$company_id)->where('Details','like',$description_string)->last();
                                if($previous_cash_entry)
                                {
                                    // start reverse entry
                                    $previously_debited = $previous_cash_entry->Debit;
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id.'hide';
                                    $cash_transaction->Credit=$previously_debited;
                                    $cash_transaction->Debit=0.00;
                                    $cash_transaction->Differentiate=$difference-$previously_debited;
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->save();
                                    // also hide previous entry start
                                    CashTransaction::where('id', $previous_cash_entry->id)->update(array('Details' => 'CashSales|'.$Id.'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // start new entry
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->save();
                                    // end new entry

                                    // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                                    // to check if there any existing entry with PartialCashSales|$id and not hidden we need to reverse that entry
                                    if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                                    {
                                        $description_string='PartialCashSales|'.$Id;
                                        $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('Description','like',$description_string)->last();
                                        if($previous_entry)
                                        {
                                            // start revers entry
                                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                            $last_closing=$accountTransaction->last()->Differentiate;
                                            $previously_credited = $previous_entry->Credit;
                                            $AccData =
                                                [
                                                    'customer_id' => $sold->customer_id,
                                                    'Debit' => $previously_credited,
                                                    'Credit' => 0.00,
                                                    'Differentiate' => $last_closing+$previously_credited,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'PartialCashSales|'.$Id,
                                                    'updateDescription'=>'hide',
                                                ];
                                            $AccountTransactions = AccountTransaction::Create($AccData);
                                            // also hide previous entry start
                                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                            // also hide previous entry end
                                            // reverse entry done

                                            // start new entry
                                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                            $last_closing=$accountTransaction->last()->Differentiate;
                                            $AccData =
                                                [
                                                    'customer_id' => $sold->customer_id,
                                                    'Debit' => 0.00,
                                                    'Credit' => $request->Data['paidBalance'],
                                                    'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'PartialCashSales|'.$Id,
                                                ];
                                            $AccountTransactions = AccountTransaction::Create($AccData);
                                            // new entry done
                                        }
                                    }
                                }
                                else
                                {
                                    // start new entry
                                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                    $difference = $cashTransaction->last()->Differentiate;
                                    $cash_transaction = new CashTransaction();
                                    $cash_transaction->Reference=$Id;
                                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                                    $cash_transaction->Type='sales';
                                    $cash_transaction->Details='CashSales|'.$Id;
                                    $cash_transaction->Credit=0.00;
                                    $cash_transaction->Debit=$request->Data['paidBalance'];
                                    $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                    $cash_transaction->user_id = $user_id;
                                    $cash_transaction->company_id = $company_id;
                                    $cash_transaction->PadNumber = $request->Data['orders'][0]['PadNumber'];
                                    $cash_transaction->save();
                                    // end new entry

                                    // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                                    // to create one more cash entry for this sales as partial cash sales entry in account transaction
                                    if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                                    {
                                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                        $last_closing=$accountTransaction->last()->Differentiate;
                                        $description_string='Sales|'.$Id;
                                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                        //echo "<pre>";print_r($previous_entry->Credit);die;
                                        $previously_debited = $previous_entry->Debit;
                                        $AccData =
                                            [
                                                'customer_id' => $sold->customer_id,
                                                'Debit' => 0.00,
                                                'Credit' => $request->Data['paidBalance'],
                                                'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                                'createdDate' => $request->Data['SaleDate'],
                                                'user_id' => $user_id,
                                                'company_id' => $company_id,
                                                'Description'=>'PartialCashSales|'.$Id,
                                            ];
                                        $AccountTransactions = AccountTransaction::Create($AccData);
                                    }
                                }
                            }

                            // here will come 3 cases
                            // 1. only customer is updating - quantity and price remains same
                            // 2. only quantity or price updating - customer is the same
                            // 3. both customer and quantity or price updating

                            // 1 check if only customer is changed and not quantity or price = grand total is same as previous
                            if($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                            {
                                //customer is changed need to reverse all previously made account entries for the previous customer

                                //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                                if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                                {
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                }
                                //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                                elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='PartialCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }
                                //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                                else
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='FullCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'FullCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }

                                /*new entry*/
                                // start new entry for updated customer with checking all three cases
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                // totally credit
                                if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $totalCredit,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // partial payment some cash some credit
                                elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                                {
                                    $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                    $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                    $difference = $differenceValue + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $request->Data['grandTotal'],
                                            'Differentiate' => $totalCredit,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$totalCredit-$request->Data['paidBalance'];
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => $request->Data['paidBalance'],
                                            'Debit' => 0.00,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                            'referenceNumber'=>'PartialCashSales#'.$request->Data['orders'][0]['PadNumber'],
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // fully paid with cash
                                else
                                {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalCredit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ]);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$difference-$request->Data['paidBalance'];
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $request->Data['paidBalance'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                        'referenceNumber'=>'FullCashSales#'.$request->Data['orders'][0]['PadNumber'],
                                    ]);
                                }
                                /*new entry*/
                            }
                            // check if only grand total is changed and not the customer
                            elseif($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                            {
                                //customer is not changed then need to find what is the differance in total and for payment changes
                                // here in two way we can proceed
                                // option 1 : reverse previous account entries and make new entry
                                // option 2 : find out plus minus differance and make one another entry with differences
                                // option 2 is not preferable because of while displaying we need to add or subtract similar sales id entry so that is little tricky in query
                                // also need to manage isPaid and isPartialPaid flag according

                                // implementation of option 2
                                //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                                if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                                {
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => date('Y-m-d'),
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                }
                                //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                                elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='PartialCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }
                                //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                                else
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='FullCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'FullCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }

                                /*new entry*/
                                // start new entry for updated customer with checking all three cases
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                // totally credit
                                if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $totalCredit,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // partial payment some cash some credit
                                elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                                {
                                    $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                    $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                    $difference = $differenceValue + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $request->Data['grandTotal'],
                                            'Differentiate' => $totalCredit,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$totalCredit-$request->Data['paidBalance'];
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => $request->Data['paidBalance'],
                                            'Debit' => 0.00,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // fully paid with cash or there may be some advance amount remains
                                else
                                {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalCredit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                    ]);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$difference-$request->Data['paidBalance'];
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $request->Data['paidBalance'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                    ]);
                                }
                                /*new entry*/
                            }
                            // check both supplier and grandTotal is changed meaning case 3
                            elseif($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                            {
                                // if paid balance is not same as earlier need to update cash account as well
                                //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                                if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                                {
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                }
                                //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                                elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done
                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='PartialCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }
                                //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                                else
                                {
                                    // entry 1 : debit entry for sales
                                    // start reverse entry
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Sales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing-$previously_debited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // entry 2 : credit whatever cash is debited
                                    // start reverse entry
                                    $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='FullCashSales|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                    $previously_credited = $previous_entry->Credit;
                                    $AccData =
                                        [
                                            'customer_id' => $sold->customer_id,
                                            'Debit' => $previously_credited,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing+$previously_credited,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'FullCashSales|'.$Id,
                                            'updateDescription'=>'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // reverse cash entry start
                                    // reverse cash entry end
                                    // no need to make cash entries because amount is same only supplier is changing
                                    // make new cash entry for correct supplier start
                                    // make new cash entry for correct supplier end
                                }

                                /*new entry*/
                                // start new entry for updated customer with checking all three cases
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                // totally credit
                                if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $totalCredit,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // partial payment some cash some credit
                                elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                                {
                                    $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                    $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                    $difference = $differenceValue + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $request->Data['grandTotal'],
                                            'Differentiate' => $totalCredit,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$totalCredit-$request->Data['paidBalance'];
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => $request->Data['paidBalance'],
                                            'Debit' => 0.00,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashSales|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // fully paid with cash
                                else
                                {
                                    $totalCredit = $request->Data['grandTotal'];
                                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                    //make credit entry for the sales
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalCredit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                    ]);

                                    //make debit entry for the whatever cash is paid
                                    $difference=$difference-$request->Data['paidBalance'];
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $request->Data['paidBalance'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                    ]);
                                }
                                /*new entry*/
                            }
                        }
                    }
                    //return Response()->json($accountTransaction);
                }
                ////////////////// end of account section gautam ////////////////

                if($sold->IsPaid==1 && $sold->IsPartialPaid==0)
                {
                    $sold->update(
                        [
                            'SaleNumber' => $request->Data['SaleNumber'],
                            'SaleDate' => $request->Data['SaleDate'],
                            'Total' => $request->Data['Total'],
                            'subTotal' => $request->Data['subTotal'],
                            'totalVat' => $request->Data['totalVat'],
                            'grandTotal' => $request->Data['grandTotal'],
//                        'paidBalance' => $request->Data['paidBalance'],
//                        'remainingBalance' => $request->Data['remainingBalance'],
                            'customer_id' => $request->Data['customer_id'],
                            'Description' => $request->Data['Description'],
//                        'IsPaid' => $isPaid,
//                        'IsPartialPaid' => $partialPaid,
                            'IsReturn' => false,
                            'IsPartialReturn' => false,
                            'IsNeedStampOrSignature' => false,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                        ]);

                    $update_note = new UpdateNote();
                    $update_note->RelationTable = 'sales';
                    $update_note->RelationId = $Id;
                    $update_note->Description = $request->Data['UpdateDescription'];
                    $update_note->user_id = $user_id;
                    $update_note->company_id = $company_id;
                    $update_note->save();

                    $d = SaleDetail::where('sale_id', array($Id))->delete();
                    $slct = SaleDetail::where('sale_id', $Id)->get();
                    foreach ($request->Data['orders'] as $detail)
                    {
                        $saleDetails = SaleDetail::create([
                            //"Id" => $detail['Id'],
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
                            "vehicle_id"        => $detail['vehicle_id'],
                            "Quantity"        => $detail['Quantity'],
                            "Price"        => $detail['Price'],
                            "rowTotal"        => $detail['rowTotal'],
                            "VAT"        => $detail['Vat'],
                            "rowVatAmount"        => $detail['rowVatAmount'],
                            "rowSubTotal"        => $detail['rowSubTotal'],
                            "PadNumber"        => $detail['PadNumber'],
                            "company_id" => $company_id,
                            "user_id"      => $user_id,
                            "sale_id"      => $Id,
                            "createdDate" => $detail['createdDate'],
                            "customer_id" => $request->Data['customer_id'],
                        ]);
                    }
                    $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                    return Response()->json($ss);
                }
                elseif($sold->IsPaid==0 && $sold->IsPartialPaid==1)
                {
                    $sold->update(
                        [
                            'SaleNumber' => $request->Data['SaleNumber'],
                            'SaleDate' => $request->Data['SaleDate'],
                            'Total' => $request->Data['Total'],
                            'subTotal' => $request->Data['subTotal'],
                            'totalVat' => $request->Data['totalVat'],
                            'grandTotal' => $request->Data['grandTotal'],
//                        'paidBalance' => $request->Data['paidBalance'],
//                        'remainingBalance' => $request->Data['remainingBalance'],
                            'customer_id' => $request->Data['customer_id'],
                            'Description' => $request->Data['Description'],
//                        'IsPaid' => $isPaid,
//                        'IsPartialPaid' => $partialPaid,
                            'IsReturn' => false,
                            'IsPartialReturn' => false,
                            'IsNeedStampOrSignature' => false,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                        ]);

                    $update_note = new UpdateNote();
                    $update_note->RelationTable = 'sales';
                    $update_note->RelationId = $Id;
                    $update_note->Description = $request->Data['UpdateDescription'];
                    $update_note->user_id = $user_id;
                    $update_note->company_id = $company_id;
                    $update_note->save();

                    $d = SaleDetail::where('sale_id', array($Id))->delete();
                    $slct = SaleDetail::where('sale_id', $Id)->get();
                    foreach ($request->Data['orders'] as $detail)
                    {
                        $saleDetails = SaleDetail::create([
                            //"Id" => $detail['Id'],
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
                            "vehicle_id"        => $detail['vehicle_id'],
                            "Quantity"        => $detail['Quantity'],
                            "Price"        => $detail['Price'],
                            "rowTotal"        => $detail['rowTotal'],
                            "VAT"        => $detail['Vat'],
                            "rowVatAmount"        => $detail['rowVatAmount'],
                            "rowSubTotal"        => $detail['rowSubTotal'],
                            "PadNumber"        => $detail['PadNumber'],
                            "company_id" => $company_id,
                            "user_id"      => $user_id,
                            "sale_id"      => $Id,
                            "createdDate" => $detail['createdDate'],
                            "customer_id" => $request->Data['customer_id'],
                        ]);
                    }
                    $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                    return Response()->json($ss);
                }
                else
                {
                    if($request->Data['lastClosing']<0)
                    {
                        $sold->update(
                            [
                                'SaleNumber' => $request->Data['SaleNumber'],
                                'SaleDate' => $request->Data['SaleDate'],
                                'Total' => $request->Data['Total'],
                                'subTotal' => $request->Data['subTotal'],
                                'totalVat' => $request->Data['totalVat'],
                                'grandTotal' => $request->Data['grandTotal'],
                                //'paidBalance' => $request->Data['paidBalance'],
                                //'remainingBalance' => $request->Data['remainingBalance'],
                                'customer_id' => $request->Data['customer_id'],
                                //'Description' => $request->Data['Description'],
                                //'IsPaid' => $isPaid,
                                //'IsPartialPaid' => $partialPaid,
                                'IsReturn' => false,
                                'IsPartialReturn' => false,
                                'IsNeedStampOrSignature' => false,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                            ]);

                        $update_note = new UpdateNote();
                        $update_note->RelationTable = 'sales';
                        $update_note->RelationId = $Id;
                        $update_note->Description = $request->Data['UpdateDescription'];
                        $update_note->user_id = $user_id;
                        $update_note->company_id = $company_id;
                        $update_note->save();

                        $d = SaleDetail::where('sale_id', array($Id))->delete();
                        $slct = SaleDetail::where('sale_id', $Id)->get();
                        foreach ($request->Data['orders'] as $detail)
                        {
                            $saleDetails = SaleDetail::create([
                                //"Id" => $detail['Id'],
                                "product_id"        => $detail['product_id'],
                                "unit_id"        => $detail['unit_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $Id,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                        $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                        return Response()->json($ss);
                    }
                    else
                    {
                        //here will come cash transaction record update if scenario will come by
                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                            $isPaid = false;
                            $partialPaid =false;
                        }
                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                        {
                            $isPaid = true;
                            $partialPaid =false;
                        }
                        else
                        {
                            $isPaid = false;
                            $partialPaid =true;
                        }

                        $sold->update(
                            [
                                'SaleNumber' => $request->Data['SaleNumber'],
                                'SaleDate' => $request->Data['SaleDate'],
                                'Total' => $request->Data['Total'],
                                'subTotal' => $request->Data['subTotal'],
                                'totalVat' => $request->Data['totalVat'],
                                'grandTotal' => $request->Data['grandTotal'],
                                'paidBalance' => $request->Data['paidBalance'],
                                'remainingBalance' => $request->Data['grandTotal'],
                                'customer_id' => $request->Data['customer_id'],
                                'Description' => $request->Data['Description'],
                                'IsPaid' => $isPaid,
                                'IsPartialPaid' => $partialPaid,
                                'IsReturn' => false,
                                'IsPartialReturn' => false,
                                'IsNeedStampOrSignature' => false,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                            ]);

                        $update_note = new UpdateNote();
                        $update_note->RelationTable = 'sales';
                        $update_note->RelationId = $Id;
                        $update_note->Description = $request->Data['UpdateDescription'];
                        $update_note->user_id = $user_id;
                        $update_note->company_id = $company_id;
                        $update_note->save();

                        $d = SaleDetail::where('sale_id', array($Id))->delete();
                        $slct = SaleDetail::where('sale_id', $Id)->get();
                        foreach ($request->Data['orders'] as $detail)
                        {
                            $saleDetails = SaleDetail::create([
                                //"Id" => $detail['Id'],
                                "product_id"        => $detail['product_id'],
                                "unit_id"        => $detail['unit_id'],
                                "vehicle_id"        => $detail['vehicle_id'],
                                "Quantity"        => $detail['Quantity'],
                                "Price"        => $detail['Price'],
                                "rowTotal"        => $detail['rowTotal'],
                                "VAT"        => $detail['Vat'],
                                "rowVatAmount"        => $detail['rowVatAmount'],
                                "rowSubTotal"        => $detail['rowSubTotal'],
                                "PadNumber"        => $detail['PadNumber'],
                                "company_id" => $company_id,
                                "user_id"      => $user_id,
                                "sale_id"      => $Id,
                                "createdDate" => $detail['createdDate'],
                                "customer_id" => $request->Data['customer_id'],
                            ]);
                        }
                        $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                        return Response()->json($ss);
                    }
                }
            }
        });

    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function CheckPadExist($request)
    {
        $data = SaleDetail::where('PadNumber','=',$request->PadNumber)->where('company_id','=',session('company_id'))->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'sales'])->get();
        //dd($update_notes[0]->Description);
        $customers = Customer::where('company_id',session('company_id'))->get();
        $products = Product::all();
        $units = Unit::all();
        $sale_details = SaleDetail::withTrashed()->with('sale.customer.customer_prices','user','product','unit','vehicle','customer')->where('sale_id', $Id)->get();
        return view('admin.sale.edit',compact('sale_details','customers','products','update_notes','units'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Sale::findOrFail($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        //echo "<pre>";print_r($data);die;
        //$data->delete();
        /*if($data)
        {
            if($data->IsPaid==1 && $data->IsPartialPaid==0)
            {
                //full cash sales case
                $prev_accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,'Description'=>'Sales|'.$data->id])->get()->first();
                if($prev_accountTransaction)
                {
                    //echo "<pre>";print_r($prev_accountTransaction);die;
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $data->customer_id,
                        'Credit' => $prev_accountTransaction->Debit,
                        'Debit' => 0.00,
                        'Differentiate' => $last_closing-$prev_accountTransaction->Debit,
                        'createdDate' => $data->SaleDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'SalesReverse|'.$data->id,
                        'referenceNumber'=>$prev_accountTransaction->referenceNumber,
                        'updateDescription'=>'hide',
                    ]);
                    $prev_payment_accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,'Description'=>'FullCashSales|'.$data->id])->get()->first();
                    if($prev_payment_accountTransaction)
                    {
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,])->get();
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $data->customer_id,
                            'Credit' => 0.00,
                            'Debit' => $prev_payment_accountTransaction->Credit,
                            'Differentiate' => $last_closing+$prev_payment_accountTransaction->Credit,
                            'createdDate' => $data->SaleDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSalesReverse|'.$data->id,
                            'referenceNumber'=>$prev_accountTransaction->referenceNumber,
                            'updateDescription'=>'hide',
                        ]);

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$data->id;
                        $cash_transaction->createdDate=$data->SaleDate;
                        $cash_transaction->Type='sales';
                        $cash_transaction->Details='CashSalesReversal|'.$data->id;
                        $cash_transaction->Credit=$prev_payment_accountTransaction->Credit;
                        $cash_transaction->Debit=0.00;
                        $cash_transaction->Differentiate=$difference-$prev_payment_accountTransaction->Credit;
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->PadNumber = $prev_accountTransaction->referenceNumber;
                        $cash_transaction->save();

                        // delete sales and sales detail entries
                        SaleDetail::where('sale_id',$data->id)->delete();
                        $data->delete();
                    }
                }
            }
            elseif($data->IsPaid==0 && $data->IsPartialPaid==0)
            {
                //full credit entry
                $prev_accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,'Description'=>'Sales|'.$data->id])->get()->first();
                if($prev_accountTransaction)
                {
                    //echo "<pre>";print_r($prev_accountTransaction);die;
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $data->customer_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $data->customer_id,
                        'Credit' => $prev_accountTransaction->Debit,
                        'Debit' => 0.00,
                        'Differentiate' => $last_closing-$prev_accountTransaction->Debit,
                        'createdDate' => $data->SaleDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'SalesReverse|'.$data->id,
                        'referenceNumber'=>$prev_accountTransaction->referenceNumber,
                        'updateDescription'=>'hide',
                    ]);

                    // delete sales and sales detail entries
                    SaleDetail::where('sale_id',$data->id)->delete();
                    $data->delete();
                }
            }
            return redirect()->route('sales.index');
        }
        else
        {
            return redirect()->route('sales.index');
        }*/
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function invoiceNumber()
    {
        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
//        $PadNumber = new SaleDetail();
//        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);
//        return $newPad;

        //new pad number generation according to company last pad
//        $PadNumber = new SaleDetail();
//        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        if(!is_numeric($lastPad))
//        {
//            $newPad=1;
//        }
//        else
//        {
//            $newPad = ($lastPad + 1);
//        }
//        return $newPad;$PadNumber = new SaleDetail();
//        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        if(!is_numeric($lastPad))
//        {
//            $newPad=1;
//        }
//        else
//        {
//            $newPad = ($lastPad + 1);
//        }
//        return $newPad;

        // pad number according to max sales id
        $data=array();
        $max_sales_id = SaleDetail::where('company_id',session('company_id'))->max('id');
        $max_sales_id = SaleDetail::where('id',$max_sales_id)->first();
        //echo "<pre>";print_r($max_sales_id);die;
        if($max_sales_id)
        {
            $lastPad = $max_sales_id->PadNumber;
            $lastDate = $max_sales_id->createdDate;
            if(!is_numeric($lastPad))
            {
                $data['pad_no']=1;
                $data['last_date']=$lastDate;
            }
            else
            {
                $data['pad_no']=$lastPad + 1;
                $data['last_date']=$lastDate;
            }
        }
        else
        {
            $data['pad_no']=1;
            $data['last_date']=date('Y-m-d');
        }
        return $data;
    }
}
