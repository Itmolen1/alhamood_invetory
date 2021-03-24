<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use App\Http\Requests\PaymentReceiveRequest;
use App\Http\Resources\PaymentReceive\PaymentReceiveResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\PaymentReceive;
use App\Models\PaymentReceiveDetail;
use App\Models\PaymentType;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentReceiveRepository implements IPaymentReceiveRepositoryInterface
{

    public function all()
    {
        return PaymentReceiveResource::collection(PaymentReceive::with('user')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return PaymentReceiveResource::Collection(PaymentReceive::with('payment_receive_details')->where('company_id',$company_id)->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        /* start of new code*/
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);

        $payment_receive = new PaymentReceive();
        $payment_receive->totalAmount=$request->totalAmount;
        $payment_receive->paidAmount=$request->paidAmount;
        $payment_receive->amountInWords=Str::getUAECurrency($request->paidAmount);
        $payment_receive->customer_id=$request->customer_id;
        $payment_receive->bank_id=$request->bank_id;
        $payment_receive->accountNumber=$request->accountNumber;
        $payment_receive->transferDate=$request->transferDate;
        $payment_receive->payment_type=$request->payment_type;
        $payment_receive->referenceNumber=$request->referenceNumber;
        $payment_receive->receiverName=$request->receiverName;
        $payment_receive->receiptNumber=$request->receiptNumber;
        $payment_receive->Description=$request->Description;
        $payment_receive->paymentReceiveDate=$request->paymentReceiveDate;
        $payment_receive->createdDate=date('Y-m-d h:i:s');
        $payment_receive->isActive=1;
        $payment_receive->user_id = $user_id ?? 0;
        $payment_receive->company_id=$company_id;
        $payment_receive->save();
        $payment_receive_id = $payment_receive->id;

        $total_i_have=$request->paidAmount;
        $payment_receive_details=json_decode($_POST['payment_receive_details']);
        foreach ($payment_receive_details as $detail)
        {
            $this_sale=Sale::where('id',$detail->sale_id)->get()->first();
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
                    "sale_id" => $detail->sale_id,
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "payment_receive_id" => $payment_receive_id,
                    'createdDate' => $request->paymentReceiveDate,
                ]);
                if($total_i_have<=0)
                {
                    break;
                }
            }
        }
        /* end of new code */
        $Response = PaymentReceiveResource::collection(PaymentReceive::where('id',$payment_receive->id)->with('payment_receive_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function update(Request $request, $Id)
    {
        $user_id = Auth::id();
        $payment_receive = PaymentReceive::find($Id);

        $payment_receive->update([
            'paidAmount' => $request->paidAmount,
            'payment_type' => $request->payment_type,
            'bank_id' => $request->bank_id,
            'accountNumber' => $request->accountNumber,
            'TransferDate' => $request->TransferDate,
            'referenceNumber' => $request->referenceNumber,
            'paymentReceiveDate' => $request->paymentReceiveDate,
            'Description' => $request->Description,
            'amountInWords' => Str::getUAECurrency($request->paidAmount),
            'receiverName' => $request->receiverName,
            'user_id' => $user_id,
        ]);
        $Response = PaymentReceiveResource::collection(PaymentReceive::where('id',$Id)->with('payment_receive_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return array('customer'=>Customer::select('id','Name')->where('company_id',$company_id)->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'bank'=>Bank::select('id','Name','Description')->orderBy('id','desc')->get());
    }

    public function customer_payments_push($Id)
    {
        /* start of new code */
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

        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);

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
        /* end of new code */

        return TRUE;
    }
}
