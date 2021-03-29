<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
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
        if(request()->ajax())
        {
            return datatables()->of(PaymentReceive::with('user','company','customer')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<a href="'.route('payment_receives.show', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-bars"></i></a>';
                    $button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'">Show Details</button>';
//                    $button .= '&nbsp;&nbsp;';
//                    $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                    $button .='&nbsp;';
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
                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .= '&nbsp;';
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
                        'referenceNumber',
                        'paymentReceiveDate',
                    ])
                ->make(true);
        }
        return view('admin.customer_payment_receive.index');
    }

    public function create()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        $banks = Bank::all();
        return view('admin.customer_payment_receive.create',compact('customers','banks'));
    }

    public function store(Request $request)
    {
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
            $total_i_have=$request->Data['paidAmount'];
            foreach($request->Data['orders'] as $detail)
            {
                $this_sale=Sale::where('id',$detail['sale_id'])->get()->first();
                if($this_sale->IsPaid==0 AND $this_sale->remainingBalance!=0)
                {
                    $total_you_need = $this_sale->remainingBalance;
                    $still_payable_to_you=0;
                    $total_giving_to_you=0;
                    $isPartialPaid = 0;
                    if ($total_i_have >= $total_you_need)
                    {
                        $total_i_have = $total_i_have - $total_you_need;
                        $total_giving_to_you=$total_you_need;
                    }
                    else
                    {
                        $total_giving_to_you=$total_i_have;
                        $total_i_have = $total_i_have - $total_giving_to_you;
                    }
                    PaymentReceiveDetail::create([
                        "amountPaid" => $total_giving_to_you,
                        "sale_id" => $detail['sale_id'],
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "payment_receive_id" => $paymentReceive,
                        'createdDate' => $request->Data['paymentReceiveDate'],
                    ]);
                    if($total_i_have<=0)
                    {
                        break;
                    }
                }
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
        $payment_receive = PaymentReceive::find($Id);
        $user_id = session('user_id');
        //echo "<pre>";print_r($request->all());die;
        $payment_receive->update([
            'payment_type' => $request->Data['payment_type'],
            'bank_id' => $request->Data['bank_id'],
            'accountNumber' => $request->Data['accountNumber'],
            'TransferDate' => $request->Data['TransferDate'],
            'receiptNumber' => $request->Data['receiptNumber'],
            'paymentReceiveDate' => $request->Data['paymentReceiveDate'],
            'Description' => $request->Data['Description'],
            'amountInWords' => $request->Data['amountInWords'],
            'receiverName' => $request->Data['receiverName'],
            'user_id' => $user_id,
        ]);
        return redirect()->route('payment_receives.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        $payment_receives_details = PaymentReceiveDetail::with('user','company','payment_receive.customer')->where('payment_receive_id',$Id)->get();
//        dd($payment_receives);
        return view('admin.customer_payment_receive.show',compact('payment_receives_details'));
    }

    public function getCustomerPaymentDetail($Id)
    {
        $payment=PaymentReceive::with(['customer'])->where('id',$Id)->first();
        $payment_detail=PaymentReceiveDetail::with(['sale','sale.sale_details'])->where('payment_receive_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Supplier Name : '.$payment->customer->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Date : '.date('d-M-Y',strtotime($payment->paymentReceiveDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$payment->payment_type.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$payment->referenceNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$payment->paidAmount.'</label></div></div>';
        $html.='<table class="table"><thead><th>SR</th><th>Sale Date</th><th>PAD</th><th>Total</th><th>Paid</th><th>Balance</th></thead><tbody>';
        $i=0;
        foreach ($payment_detail as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->sale->SaleDate))??"NA".'</td>';
            $html.='<td>'.$item->sale->sale_details[0]->PadNumber??"NA".'</td>';
            $html.='<td>'.$item->sale->grandTotal??"NA".'</td>';
            $html.='<td>'.$item->sale->paidBalance??"NA".'</td>';
            $html.='<td>'.$item->sale->remainingBalance??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json($html);
    }

    public function edit($Id)
    {
        $customers = Customer::all();
        $banks = Bank::all();
        $payment_receive = PaymentReceive::with('user','company','customer','payment_receive_details.sale.sale_details')->find($Id);
        //echo $payment_receive->payment_type;die;
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
        $payments = PaymentReceive::with('customer','payment_receive_details')->find($Id);

        foreach($payments->payment_receive_details as $single)
        {
            $sales=Sale::where('id',$single->sale_id)->get()->first();
            $is_paid=0;
            if($sales->remainingBalance-$single->amountPaid==0)
            {
                $is_paid=1;
                $is_partial_paid=0;
            }
            else
            {
                $is_partial_paid=1;
            }
            $sales->update([
                'paidBalance'=>$sales->paidBalance+$single->amountPaid,
                'remainingBalance'=>$sales->remainingBalance-$single->amountPaid,
                'Description'=>$payments->referenceNumber,
                'IsPaid'=>$is_paid,
                'IsPartialPaid'=>$is_partial_paid,
            ]);
        }

        $user_id = session('user_id');
        $company_id = session('company_id');
        $payments->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        $accountTransaction_ref=0;

        if($payments->payment_type == 'cash')
        {
            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
            $difference = $cashTransaction->last()->Differentiate;
            $cash_transaction = new CashTransaction();
            $cash_transaction->Reference=$Id;
            $cash_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
            $cash_transaction->Type='payment_receives';
            $cash_transaction->Details='CustomerCashPayment|'.$Id;
            $cash_transaction->Credit=0.00;
            $cash_transaction->Debit=$payments->paidAmount;
            $cash_transaction->Differentiate=$difference+$payments->paidAmount;
            $cash_transaction->user_id = $user_id;
            $cash_transaction->company_id = $company_id;
            $cash_transaction->PadNumber = $payments->referenceNumber;
            $cash_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'customer_id' => $payments->customer_id,
                    'Debit' => 0.00,
                    'Credit' => $payments->paidAmount,
                    'Differentiate' => $last_closing-$payments->paidAmount,
                    'createdDate' => $payments->transferDate,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'CustomerCashPayment|'.$Id,
                    'referenceNumber'=>$payments->referenceNumber,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            $accountTransaction_ref=$AccountTransactions->id;
            // new entry done
        }
        elseif ($payments->payment_type == 'bank')
        {
            $bankTransaction = BankTransaction::where(['bank_id'=> $payments->bank_id])->get();
            $difference = $bankTransaction->last()->Differentiate;
            $bank_transaction = new BankTransaction();
            $bank_transaction->Reference=$Id;
            $bank_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
            $bank_transaction->Type='payment_receives';
            $bank_transaction->Details='CustomerBankPayment|'.$Id;
            $bank_transaction->Credit=0.00;
            $bank_transaction->Debit=$payments->paidAmount;
            $bank_transaction->Differentiate=$difference+$payments->paidAmount;
            $bank_transaction->user_id = $user_id;
            $bank_transaction->company_id = $company_id;
            $bank_transaction->bank_id = $payments->bank_id;
            $bank_transaction->updateDescription = $payments->referenceNumber;
            $bank_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'customer_id' => $payments->customer_id,
                    'Debit' => 0.00,
                    'Credit' => $payments->paidAmount,
                    'Differentiate' => $last_closing-$payments->paidAmount,
                    'createdDate' => $payments->transferDate,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'CustomerBankPayment|'.$Id,
                    'referenceNumber'=>$payments->referenceNumber,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            $accountTransaction_ref=$AccountTransactions->id;
            // new entry done
        }
        elseif ($payments->payment_type == 'cheque')
        {
            $bankTransaction = BankTransaction::where(['bank_id'=> $payments->bank_id])->get();
            $difference = $bankTransaction->last()->Differentiate;
            $bank_transaction = new BankTransaction();
            $bank_transaction->Reference=$Id;
            $bank_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
            $bank_transaction->Type='payment_receives';
            $bank_transaction->Details='CustomerChequePayment|'.$Id;
            $bank_transaction->Credit=0.00;
            $bank_transaction->Debit=$payments->paidAmount;
            $bank_transaction->Differentiate=$difference+$payments->paidAmount;
            $bank_transaction->user_id = $user_id;
            $bank_transaction->company_id = $company_id;
            $bank_transaction->bank_id = $payments->bank_id;
            $bank_transaction->updateDescription = $payments->referenceNumber;
            $bank_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'customer_id' => $payments->customer_id,
                    'Debit' => 0.00,
                    'Credit' => $payments->paidAmount,
                    'Differentiate' => $last_closing-$payments->paidAmount,
                    'createdDate' => $payments->transferDate,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'CustomerChequePayment|'.$Id,
                    'referenceNumber'=>$payments->referenceNumber,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            $accountTransaction_ref=$AccountTransactions->id;
            // new entry done
        }

        //now since account is affected we need to auto pay same amount to sales entries only if last closing is positive value

        /*if($payments->paidAmount>0)
        {
            //we have entries without payment made so make it paid until payment amount becomes zero
            // bring all unpaid sales records
            $all_sales = Sale::with('customer','sale_details')->where([
                'customer_id'=>$payments->customer_id,
                'IsPaid'=> false,
            ])->orderBy('SaleDate')->get();
            //echo "<pre>";print_r($all_sales);die;
            $total_i_have=$payments->paidAmount;

            foreach($all_sales as $sale)
            {
                $total_you_need = $sale->remainingBalance;
                $still_payable_to_you=0;
                $total_giving_to_you=0;
                $isPartialPaid = 0;
                if ($total_i_have >= $total_you_need)
                {
                    $isPaid = 1;
                    $isPartialPaid = 0;
                    $total_i_have = $total_i_have - $total_you_need;

                    $this_sale = Sale::find($sale->id);
                    $this_sale->update([
                        "paidBalance"        => $sale->grandTotal,
                        "remainingBalance"   => $still_payable_to_you,
                        "IsPaid" => $isPaid,
                        "IsPartialPaid" => $isPartialPaid,
                        "IsNeedStampOrSignature" => false,
                        "Description" => 'AutoPaid|'.$payments->id,
                        "account_transaction_payment_id" => $accountTransaction_ref,
                    ]);
                }
                else
                {
                    $isPaid = 0;
                    $isPartialPaid = 1;
                    $total_giving_to_you=$total_i_have;
                    $total_i_have = $total_i_have - $total_giving_to_you;

                    $this_sale = Sale::find($sale->id);
                    $this_sale->update([
                        "paidBalance"        => $sale->paidBalance+$total_giving_to_you,
                        "remainingBalance"   => $sale->remainingBalance-$total_giving_to_you,
                        "IsPaid" => $isPaid,
                        "IsPartialPaid" => $isPartialPaid,
                        "IsNeedStampOrSignature" => false,
                        "Description" => 'AutoPaid|'.$payments->id,
                        "account_transaction_payment_id" => $accountTransaction_ref,
                    ]);
                }

                if($total_i_have<=0)
                {
                    break;
                }
            }
        }*/

        /*////////////////// account section ////////////////
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
                $totalDebit = $paymets->paidAmount;
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
        /// */
        return redirect()->route('payment_receives.index')->with('pushed','Your Account Debit Successfully');
    }
}
