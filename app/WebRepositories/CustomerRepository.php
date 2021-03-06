<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerPrice;
use App\Models\Product;
use App\Models\Region;
use App\Models\PaymentType;
use App\Models\PaymentTerm;
use App\Models\CompanyType;
use App\Models\AccountTransaction;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Unit;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements ICustomerRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Customer::with('company','user','payment_type','company_type','payment_term')->where('company_id',session('company_id'))->latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('customers.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('customers.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true)
                        {
                            $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'" checked><span class="slider"></span></label>';
                            return $button;
                        }
                        else
                        {
                            $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'"><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                 ->addColumn('paymentType', function($data) {
                        return $data->payment_type->Name ?? "No Data";
                    })
                ->rawColumns([
                    'action',
                    'isActive',
                    'paymentType'
                ])
                ->make(true);
        }
        return view('admin.customer.index');
    }

    public function create()
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        return view('admin.customer.create',compact('regions','payment_types','company_types','payment_terms'));
    }

    public function store(CustomerRequest $customerRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($customerRequest->hasFile('fileUpload'))
            $filename = $customerRequest->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = null;

        $customer = [
            'Name' =>$customerRequest->Name,
            'Mobile' =>$customerRequest->Mobile,
            'Representative' =>$customerRequest->Representative,
            'Phone' =>$customerRequest->Phone,
            'Address' =>$customerRequest->Address,
            'Email' =>$customerRequest->Email,
            'postCode' =>$customerRequest->postCode,
            'region_id' =>$customerRequest->region_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$customerRequest->Description,
            'registrationDate' =>$customerRequest->registrationDate,
            'TRNNumber' =>$customerRequest->TRNNumber,
            'openingBalance' =>$customerRequest->openingBalance,
            'openingBalanceAsOfDate' =>$customerRequest->openingBalanceAsOfDate,
            'payment_term_id' =>$customerRequest->paymentTerm ?? 0,
            'company_type_id' =>$customerRequest->companyType ?? 0,
            'payment_type_id' =>$customerRequest->paymentType ?? 0,
        ];
        $customer = Customer::create($customer);
        if ($customer)
        {
            //account entry
            $account = new AccountTransaction([
                'customer_id' => $customer->id,
                'user_id' => $user_id,
                'createdDate' => $customerRequest->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Description' =>'initial',
                'referenceNumber' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$customerRequest->openingBalance,
            ]);
            $customer->account_transaction()->save($account);

            if($customerRequest->companyType==1 && $customerRequest->openingBalance!=0)
            {
                //sales entry
                $sale = new Sale();
                $sale->SaleNumber = 'initial';
                $sale->SaleDate = $customerRequest->openingBalanceAsOfDate;
                $sale->Total = $customerRequest->openingBalance;
                $sale->subTotal = $customerRequest->openingBalance;
                $sale->totalVat = 0.00;
                $sale->grandTotal = $customerRequest->openingBalance;
                $sale->paidBalance = 0.00;
                $sale->remainingBalance = $customerRequest->openingBalance;
                $sale->customer_id = $customer->id;
                $sale->Description = '';
                $sale->IsPaid = 0;
                $sale->isActive = 0;
                $sale->IsPartialPaid = 0;
                $sale->IsReturn = false;
                $sale->IsPartialReturn = false;
                $sale->IsNeedStampOrSignature = false;
                $sale->user_id = $user_id;
                $sale->company_id = $company_id;
                $sale->save();
                $sale = $sale->id;

                $product=Product::select('id')->get()->first();
                $unit=Unit::select('id')->get()->first();


                $data =  SaleDetail::create([
                    "product_id" => $product->id,
                    "vehicle_id" => 0,
                    "unit_id" => $unit->id,
                    "Quantity" => 0.00,
                    "Price" => 0.00,
                    "rowTotal" => $customerRequest->openingBalance,
                    "VAT" => 0.00,
                    "rowVatAmount" => 0.00,
                    "rowSubTotal" => $customerRequest->openingBalance,
                    "PadNumber" => '',
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "sale_id" => $sale,
                    "createdDate" => $customerRequest->openingBalanceAsOfDate,
                    "customer_id" => $customer->id,
                ]);
            }
        }

        //also add customer base price
        $price = [
            'Rate' =>6.00,
            'VAT' =>0.00,
            'customerLimit' =>0.00,
            'user_id' =>$user_id,
            'customer_id' =>$customer->id,
            'company_id' =>$company_id,
            'pricesDate' =>date('Y-m-d'),
            'createdDate' =>date('Y-m-d'),
            'isActive' =>1,
        ];
        CustomerPrice::create($price);
        return redirect()->route('customers.index');
    }

    public function update(Request $request, $Id)
    {
        $customer = Customer::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = $customer->fileUpload;

        $user_id = session('user_id');
        $customer->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id ?? 0,
            'Email' =>$request->Email,
            'user_id' =>$user_id,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'openingBalance' =>$request->openingBalance,
            'openingBalanceAsOfDate' =>$request->openingBalanceAsOfDate,
            'payment_term_id' =>$request->paymentTerm ?? 0,
            'company_type_id' =>$request->companyType ?? 0,
            'payment_type_id' =>$request->paymentType ?? 0,

        ]);
        return redirect()->route('customers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function ChangeCustomerStatus($Id)
    {
        $customer = Customer::find($Id);
        if($customer->isActive==1)
        {
            $customer->isActive=0;
        }
        else
        {
            $customer->isActive=1;
        }
        $customer->update();
        return Response()->json(true);
    }

    public function edit($Id)
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        $customer = Customer::with('region','payment_type','company_type','payment_term')->find($Id);
        return view('admin.customer.edit',compact('customer','regions','payment_types','company_types','payment_terms'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Customer::findOrFail($Id);
        $data->delete();
        return redirect()->route('customers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }


    public function getCustomerVehicleDetails($Id)
    {
        // TODO: Implement getCustomerVehicleDetails() method.
    }

    public function customerDetails($Id)
    {
        // getting latest closing for supplier from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id')
            ->where('ac.customer_id','=',$Id)
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->get();
        $row=json_decode(json_encode($row), true);
        if(empty($row))
        {
            $row=0.00;
        }
        else
        {
            $row=$row[0]['Differentiate'];
        }

        //$customers = Customer::with('vehicles','customer_prices')->select('id','Name')->find($Id);
        $customers = Customer::with(['vehicles'=>function($q){$q->select('id','registrationNumber','customer_id');},'customer_prices'=>function($q){$q->select('id','Rate','VAT','customerLimit','customer_id');},])->select('id','Name')->where('id',$Id)->get();

        return response()->json(array('customers'=>$customers,'closing'=>$row));
    }

    public function salesCustomerDetails($Id)
    {
        // getting latest closing for supplier from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id')
            ->where('ac.customer_id','=',$Id)
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->get();
        $row=json_decode(json_encode($row), true);
        if(empty($row))
        {
            $row=0.00;
        }
        else
        {
            $row=$row[0]['Differentiate'];
        }

        //$customers = Customer::with('vehicles','customer_prices')->select('id','Name')->find($Id);
        $customers = Customer::with(['vehicles'=>function($q){$q->select('id','registrationNumber','customer_id')->where('isActive',1);},'customer_prices'=>function($q){$q->select('id','Rate','VAT','customerLimit','customer_id');},])->select('id','Name')->where('id',$Id)->get();

        return response()->json(array('customers'=>$customers,'closing'=>$row));
    }
}
