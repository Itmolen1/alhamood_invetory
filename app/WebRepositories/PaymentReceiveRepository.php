<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\PaymentReceive;
use App\Models\PaymentReceiveDetail;
use App\Models\PaymentType;
use App\Models\Sale;
use App\WebRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use Illuminate\Http\Request;

class PaymentReceiveRepository implements IPaymentReceiveRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        if(request()->ajax())
        {
            return datatables()->of(PaymentReceive::with('user','company','customer')->latest()->get())
                ->addColumn('action', function ($data) {

                    $button = '<a href="'.route('payment_receives.show', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-bars"></i></a>';
                    $button .='&nbsp;';
                    return $button;
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Quantity";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('customer_payments_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
//                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                        $button .='&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->rawColumns(
                    [
                        'action',
                         'push',
                        'customer',
                    ])
                ->make(true);
        }
        return view('admin.customer_payment_receive.index');
    }

    public function create()
    {
        // TODO: Implement create() method.
        $customers = Customer::all();
        $banks = Bank::all();
        return view('admin.customer_payment_receive.create',compact('customers','banks'));
    }

    public function store(Request $request)
    {
        // TODO: Implement store() method.
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0) {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $paymentReceive = new PaymentReceive();
            $paymentReceive->customer_id = $request->Data['customer_id'];
            $paymentReceive->totalAmount = $request->Data['totalAmount'];
            $paymentReceive->payment_type = $request->Data['payment_type'];
            $paymentReceive->referenceNumber = $request->Data['referenceNumber'];
            $paymentReceive->paymentReceiveDate = $request->Data['paymentReceiveDate'];
            $paymentReceive->paidAmount = $request->Data['paidAmount'];
            $paymentReceive->amountInWords = $request->Data['amountInWords'];
            $paymentReceive->receiptNumber = $request->Data['receiptNumber'];
            $paymentReceive->receiverName = $request->Data['receiverName'];
            $paymentReceive->transferDate = $request->Data['TransferDate'];
            $paymentReceive->accountNumber = $request->Data['accountNumber'];
            $paymentReceive->Description = $request->Data['Description'];
            $paymentReceive->bank_id = $request->Data['bank_id'] ?? 0;
            $paymentReceive->user_id = $user_id;
            $paymentReceive->createdDate = date('Y-m-d');
            $paymentReceive->company_id = $company_id;
            $paymentReceive->save();
            $paymentReceive = $paymentReceive->id;
            $amount = 0;
            foreach($request->Data['orders'] as $detail)
            {
                $amount += $detail['amountPaid'];

                if ($amount <= $request->Data['paidAmount'])
                {
                    $isPaid = true;
                    $isPartialPaid = false;
                    $totalAmount = $detail['amountPaid'];
                }
                else{
                    $isPaid = false;
                    $isPartialPaid = true;
                    $totalAmount1 = $amount - $request->Data['paidAmount'];
                    $totalAmount = $detail['amountPaid'] - $totalAmount1;
                }

                $data =  PaymentReceiveDetail::create([
                    "amountPaid"        => $totalAmount,
                    "sale_id"        => $detail['sale_id'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "payment_receive_id"      => $paymentReceive,
                    'createdDate' => date('Y-m-d')
                ]);


                $sale = Sale::find($detail['sale_id']);
                $sale->update([
                    "paidBalance"        => $totalAmount + $sale->paidBalance,
                    "remainingBalance"   => $sale->remainingBalance - $totalAmount,
                    "IsPaid" => $isPaid,
                    "IsPartialPaid" => $isPartialPaid,
                    "IsReturn" => false,
                    "IsPartialReturn" => false,
                    "IsNeedStampOrSignature" => false,
                ]);
            }
            return Response()->json($amount);
//            ////////////////// account section ////////////////
//            if ($paymentReceive)
//            {
//                $accountTransaction = AccountTransaction::where(
//                    [
//                        'customer_id'=> $request->Data['customer_id'],
//                        'createdDate' => date('Y-m-d'),
//                    ])->first();
//                if (!is_null($accountTransaction)) {
//                    if ($accountTransaction->createdDate != date('Y-m-d')) {
//                        $totalDebit = $request->Data['paidAmount'];
//                    }
//                    else
//                    {
//                        $totalDebit = $accountTransaction->Debit + $request->Data['paidAmount'];
//                    }
//                    $difference = $accountTransaction->Differentiate - $request->Data['paidAmount'];
//                }
//                else
//                {
//                    $accountTransaction = AccountTransaction::where(
//                        [
//                            'customer_id'=> $request->Data['customer_id'],
//                        ])->get();
//                    $totalDebit = $request->Data['paidAmount'];
//                    $difference = $accountTransaction->last()->Differentiate - $request->Data['paidAmount'];
//                }
//                $AccData =
//                    [
//                        'customer_id' => $request->Data['customer_id'],
//                        'Debit' => $totalDebit,
//                        'Differentiate' => $difference,
//                        'createdDate' => date('Y-m-d'),
//                        'user_id' => $user_id,
//                    ];
//                $AccountTransactions = AccountTransaction::updateOrCreate(
//                    [
//                        'createdDate'   => date('Y-m-d'),
//                        'customer_id'   => $request->Data['customer_id'],
//                    ],
//                    $AccData);
//                return Response()->json($AccountTransactions);
//                // return Response()->json("");
//            }
//            ////////////////// end of account section ////////////////
        }
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
        $payment_receives_details = PaymentReceiveDetail::with('user','company','payment_receive.customer')->where('payment_receive_id',$Id)->get();
//        dd($payment_receives);
        return view('admin.customer_payment_receive.show',compact('payment_receives_details'));
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $customers = Customer::all();
        $banks = Bank::all();
        $payment_receive = PaymentReceive::with('user','company','customer','payment_receive_details.sale.sale_details')->find($Id);
        //dd($payment_receive);
        return view('admin.customer_payment_receive.edit',compact('payment_receive','customers','banks'));

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

    public function customer_payments_push(Request $request, $Id)
    {
        // TODO: Implement customer_advances_push() method.
        $paymets = PaymentReceive::with('customer')->find($Id);
        //dd($advance->Amount);

        $user_id = session('user_id');
        $paymets->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);
        ////////////////// account section ////////////////
        if ($paymets)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $paymets->customer_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalDebit = $paymets->paidAmount;
                }
                else
                {
                    $totalDebit = $accountTransaction->Debit + $paymets->paidAmount;
                }
                $difference = $accountTransaction->Differentiate - $paymets->paidAmount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'customer_id'=> $paymets->customer_id,
                    ])->get();
                $totalDebit = $paymets->customer_id;
                $difference = $accountTransaction->last()->Differentiate - $paymets->paidAmount;
            }
            $AccData =
                [
                    'customer_id' => $paymets->customer_id,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            $AccountTransactions = AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'customer_id'   => $paymets->customer_id,
                ],
                $AccData);
            //return Response()->json($AccountTransactions);
            // return Response()->json("");
        }
        ////////////////// end of account section ////////////////
        return redirect()->route('payment_receives.index')->with('pushed','Your Account Debit Successfully');
    }
}
