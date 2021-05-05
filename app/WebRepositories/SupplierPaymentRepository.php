<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\PaymentReceive;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentRepository implements ISupplierPaymentRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(SupplierPayment::with('user','company','supplier')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<a href="'.route('supplier_payments.show', $data->id).'"  class=" btn btn-info btn-sm"><i style="font-size: 20px" class="fa fa-bars"></i></a>';
                    $button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'">Show Details</button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button class="btn btn-danger" onclick="cancel_supplier_payment(this.id)" type="button" id="cancel_'.$data->id.'">Cancel</button>';
//                    $button .='&nbsp;';
                    return $button;
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Quantity";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('supplier_payment_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
//                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                        $button .='&nbsp;';
                        $button .= '<a href="'.route('supplier_payments.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
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
                        'supplier',
                    ])
                ->make(true);
        }
        return view('admin.supplier_payment.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        $banks = Bank::all();
        return view('admin.supplier_payment.create',compact('suppliers','banks'));
    }

    public function store(Request $request)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0) {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $payment = new SupplierPayment();
            $payment->supplier_id = $request->Data['supplier_id'];
            $payment->totalAmount = $request->Data['totalAmount'];
            $payment->payment_type = $request->Data['payment_type'];
            $payment->referenceNumber = $request->Data['referenceNumber'];
            $payment->supplierPaymentDate = $request->Data['supplierPaymentDate'];
            $payment->paidAmount = $request->Data['paidAmount'];
            $payment->amountInWords = $request->Data['amountInWords'];
            $payment->receiptNumber = $request->Data['receiptNumber'];
            $payment->receiverName = $request->Data['receiverName'];
            $payment->transferDate = $request->Data['TransferDate'];
            $payment->accountNumber = $request->Data['accountNumber'];
            $payment->Description = $request->Data['Description'];
            $payment->bank_id = $request->Data['bank_id'] ?? 0;
            $payment->user_id = $user_id;
            $payment->createdDate = date('Y-m-d');
            $payment->company_id = $company_id;
            $payment->save();
            $payment = $payment->id;
            $amount = 0;
            $total_i_have=$request->Data['paidAmount'];
            foreach($request->Data['orders'] as $detail)
            {
                $this_purchase=Purchase::where('id',$detail['purchase_id'])->get()->first();
                if($this_purchase->IsPaid==0 AND $this_purchase->remainingBalance!=0)
                {
                    $total_you_need = $this_purchase->remainingBalance;
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
                    SupplierPaymentDetail::create([
                        "amountPaid" => $total_giving_to_you,
                        "purchase_id" => $detail['purchase_id'],
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "supplier_payment_id" => $payment,
                        'createdDate' => $request->Data['TransferDate'],
                    ]);
                    if($total_i_have<=0)
                    {
                        break;
                    }
                }
            }
            /*foreach($request->Data['orders'] as $detail)
            {
                $amount += $detail['amountPaid'];

                if ($amount <= $request->Data['paidAmount'])
                {
                    $isPaid = true;
                    $isPartialPaid = false;
                    $totalAmount = $detail['amountPaid'];
                }
                elseif($amount >= $request->Data['paidAmount']){
                        $isPaid = false;
                        $isPartialPaid = true;
                        $totalAmount1 = $amount - $request->Data['paidAmount'];
                        $totalAmount = $detail['amountPaid'] - $totalAmount1;
                }

                $data =  SupplierPaymentDetail::create([
                    "amountPaid"        => $totalAmount,
                    "purchase_id"        => $detail['purchase_id'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "supplier_payment_id"      => $payment,
                    'createdDate' => date('Y-m-d')
                ]);
            }*/
            return Response()->json($amount);
        }
    }

    public function edit($Id)
    {
        $supplier_payment = SupplierPayment::where('id',$Id)->get();
        $banks = Bank::all();
        //dd($supplier_payment[0]->transferDate);
        return view('admin.supplier_payment.edit',compact('supplier_payment','banks'));
    }

    public function update(Request $request, $Id)
    {
        $supplier_payment = SupplierPayment::find($Id);
        $user_id = session('user_id');
        $supplier_payment->update([
            'payment_type' => $request->paymentType,
            'bank_id' => $request->bank_id,
            'accountNumber' => $request->accountNumber,
            'TransferDate' => $request->TransferDate,
            'receiptNumber' => $request->receiptNumber,
            'supplierPaymentDate' => $request->paymentReceiveDate,
            'Description' => $request->Description,
            'amountInWords' => $request->amountInWords,
            'receiverName' => $request->receiverName,
            'user_id' => $user_id,
        ]);
        return redirect()->route('supplier_payments.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        $supplier_payment_details = SupplierPaymentDetail::with('user','company','supplier_payment.supplier','purchase')->where('supplier_payment_id',$Id)->get();
        //echo "<pre>";print_r($supplier_payment_details);die;
//        dd($payment_receives);
        return view('admin.supplier_payment.show',compact('supplier_payment_details'));
    }

    public function CheckSupplierPaymentReferenceExist($request)
    {
        $data = SupplierPayment::where('company_id',session('company_id'))->where('referenceNumber','like','%'.$request->referenceNumber.'%')->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function getSupplierPaymentDetail($Id)
    {
        $payment=SupplierPayment::with(['supplier','supplier_payment_details.purchase','supplier_payment_details.purchase.purchase_details_without_trash'])->where('id',$Id)->first();
        $payment_detail=SupplierPaymentDetail::with(['purchase','purchase.purchase_details_without_trash'])->where('supplier_payment_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Supplier Name : '.$payment->supplier->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Date : '.date('d-M-Y',strtotime($payment->supplierPaymentDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$payment->payment_type.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$payment->referenceNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$payment->paidAmount.'</label></div></div>';
        $html.='<table class="table table-sm"><thead><th>SR</th><th>Purchase Date</th><th>PAD</th><th>LPO</th><th>Total</th><th>Paid</th><th>Balance</th></thead><tbody>';
        $i=0;
        foreach ($payment_detail as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->purchase->PurchaseDate))??"NA".'</td>';
            $html.='<td>'.$item->purchase->purchase_details_without_trash[0]->PadNumber??"NA".'</td>';
            $html.='<td>'.$item->purchase->referenceNumber??"NA".'</td>';
            $html.='<td>'.$item->purchase->grandTotal??"NA".'</td>';
            $html.='<td>'.$item->purchase->paidBalance??"NA".'</td>';
            $html.='<td>'.$item->purchase->remainingBalance??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json($html);
    }

    public function cancelSupplierPayment($id)
    {
        $response=false;
        DB::transaction(function () use($id,&$response){
            $company_id = session('company_id');
            $payment=SupplierPayment::with(['supplier_payment_details'])->where('id',$id)->first();
            if($payment)
            {
                foreach($payment->supplier_payment_details as $single)
                {
                    $purchase=Purchase::where('id',$single->purchase_id)->get()->first();
                    $remaining_paid_balance=$purchase->paidBalance-$single->amountPaid;
                    $updated_remaining_balance=$purchase->remainingBalance+$single->amountPaid;

                    if($remaining_paid_balance==0.00)
                    {
                        $is_paid=0;
                        $is_partial_paid=0;
                    }
                    else
                    {
                        $is_paid=0;
                        $is_partial_paid=1;
                    }
                    $purchase->update([
                        'paidBalance'=>$remaining_paid_balance,
                        'remainingBalance'=>$updated_remaining_balance,
                        'Description'=>null,
                        'IsPaid'=>$is_paid,
                        'IsPartialPaid'=>$is_partial_paid,
                    ]);
                }

                if($payment->isPushed==1)
                {
                    if($payment->payment_type == 'cash')
                    {
                        $description_string='SupplierCashPayment|'.$id;
                        $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                        if($previous_probable_cash_entry)
                        {
                            $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_cash_entry->delete();
                        }

                        $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                        if($previous_probable_account_entry)
                        {
                            $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_account_entry->delete();
                        }
                    }
                    elseif ($payment->payment_type == 'bank')
                    {
                        $description_string='SupplierBankPayment|'.$id;
                        $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                        if($previous_probable_bank_entry)
                        {
                            $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_bank_entry->delete();
                        }

                        $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                        if($previous_probable_account_entry)
                        {
                            $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_account_entry->delete();
                        }
                    }
                    elseif ($payment->payment_type == 'cheque')
                    {
                        $description_string='SupplierChequePayment|'.$id;
                        $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                        if($previous_probable_bank_entry)
                        {
                            $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_bank_entry->delete();
                        }

                        $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                        if($previous_probable_account_entry)
                        {
                            $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                            $previous_probable_account_entry->delete();
                        }
                    }
                }
                $payment->update(['user_id'=>session('user_id')]);
                SupplierPaymentDetail::where('supplier_payment_id',$id)->update(['user_id'=>session('user_id')]);

                SupplierPaymentDetail::where('supplier_payment_id', array($id))->delete();
                SupplierPayment::where('id', array($id))->delete();
                $response=true;
            }
        });
        return Response()->json($response);
    }

    public function supplier_payments_push(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $payments = SupplierPayment::with('supplier', 'supplier_payment_details')->find($Id);

            foreach ($payments->supplier_payment_details as $single) {
                $purchase = Purchase::where('id', $single->purchase_id)->get()->first();
                $is_paid = 0;
                if ($purchase->remainingBalance - $single->amountPaid == 0) {
                    $is_paid = 1;
                    $is_partial_paid = 0;
                } else {
                    $is_partial_paid = 1;
                }
                $purchase->update([
                    'paidBalance' => $purchase->paidBalance + $single->amountPaid,
                    'remainingBalance' => $purchase->remainingBalance - $single->amountPaid,
                    'Description' => $payments->referenceNumber,
                    'IsPaid' => $is_paid,
                    'IsPartialPaid' => $is_partial_paid,
                ]);
            }

            $user_id = session('user_id');
            $company_id = session('company_id');
            $payments->update([
                'isPushed' => true,
                'user_id' => $user_id,
            ]);

            $accountTransaction_ref = 0;

            if ($payments->payment_type == 'cash') {
                $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference = $Id;
                $cash_transaction->createdDate = $payments->supplierPaymentDate ?? date('Y-m-d h:i:s');
                $cash_transaction->Type = 'supplier_payments';
                $cash_transaction->Details = 'SupplierCashPayment|' . $Id;
                $cash_transaction->Credit = $payments->paidAmount;
                $cash_transaction->Debit = 0.00;
                $cash_transaction->Differentiate = $difference - $payments->paidAmount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $payments->referenceNumber;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->supplierPaymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierCashPayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            } elseif ($payments->payment_type == 'bank') {
                $bankTransaction = BankTransaction::where(['bank_id' => $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference = $Id;
                $bank_transaction->createdDate = $payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type = 'supplier_payments';
                $bank_transaction->Details = 'SupplierBankPayment|' . $Id;
                $bank_transaction->Credit = $payments->paidAmount;
                $bank_transaction->Debit = 0.00;
                $bank_transaction->Differentiate = $difference - $payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->transferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierBankPayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            } elseif ($payments->payment_type == 'cheque') {
                $bankTransaction = BankTransaction::where(['bank_id' => $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference = $Id;
                $bank_transaction->createdDate = $payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type = 'supplier_payments';
                $bank_transaction->Details = 'SupplierChequePayment|' . $Id;
                $bank_transaction->Credit = $payments->paidAmount;
                $bank_transaction->Debit = 0.00;
                $bank_transaction->Differentiate = $difference - $payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->transferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierChequePayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            }
        });
        //now since account is affected we need to apply payment for selected entries


        /*if($payments->paidAmount>0)
        {
            //we have entries without payment made so make it paid until payment amount becomes zero
            // bring all unpaid purchase records
            $all_purchase = Purchase::with('supplier','purchase_details')->where([
                'supplier_id'=>$payments->supplier_id,
                'IsPaid'=> false,
            ])->orderBy('PurchaseDate')->get();
            //echo "<pre>";print_r($all_sales);die;
            $total_i_have=$payments->paidAmount;

            foreach($all_purchase as $purchase)
            {
                $total_you_need = $purchase->remainingBalance;
                $still_payable_to_you=0;
                $total_giving_to_you=0;
                $isPartialPaid = 0;
                if ($total_i_have >= $total_you_need)
                {
                    $isPaid = 1;
                    $isPartialPaid = 0;
                    $total_i_have = $total_i_have - $total_you_need;

                    $this_sale = Purchase::find($purchase->id);
                    $this_sale->update([
                        "paidBalance"        => $purchase->grandTotal,
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

                    $this_purchase = Purchase::find($purchase->id);
                    $this_purchase->update([
                        "paidBalance"        => $purchase->paidBalance+$total_giving_to_you,
                        "remainingBalance"   => $purchase->remainingBalance-$total_giving_to_you,
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

//        ////////////////// account section ////////////////
//        if ($payments)
//        {
//            $accountTransaction = AccountTransaction::where(
//                [
//                    'supplier_id'=> $payments->supplier_id,
//                    'createdDate' => date('Y-m-d'),
//                ])->first();
//            if (!is_null($accountTransaction)) {
//                if ($accountTransaction->createdDate != date('Y-m-d')) {
//                    $totalCredit = $payments->paidAmount;
//                }
//                else
//                {
//                    $totalCredit = $accountTransaction->Credit + $payments->paidAmount;
//                }
//                $difference = $accountTransaction->Differentiate + $payments->paidAmount;
//            }
//            else
//            {
//                $accountTransaction = AccountTransaction::where(
//                    [
//                        'supplier_id'=> $payments->supplier_id,
//                    ])->get();
//                $totalCredit = $payments->paidAmount;
//                $difference = $accountTransaction->last()->Differentiate + $payments->paidAmount;
//            }
//            $AccData =
//                [
//                    'supplier_id' => $payments->supplier_id,
//                    'Credit' => $totalCredit,
//                    'Differentiate' => $difference,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                ];
//            $AccountTransactions = AccountTransaction::updateOrCreate(
//                [
//                    'createdDate'   => date('Y-m-d'),
//                    'supplier_id'   => $payments->supplier_id,
//                ],
//                $AccData);
//            //return Response()->json($AccountTransactions);
//            // return Response()->json("");
//        }
//        ////////////////// end of account section ////////////////
        return redirect()->route('supplier_payments.index')->with('pushed','Your Account Debit Successfully');
    }
}
