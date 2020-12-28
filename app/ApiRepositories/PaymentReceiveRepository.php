<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use App\Http\Requests\PaymentReceiveRequest;
use App\Http\Resources\PaymentReceive\PaymentReceiveResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
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
        return PaymentReceiveResource::Collection(PaymentReceive::with('payment_receive_details')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
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
        $payment_receive->user_id = $userId ?? 0;
        $payment_receive->company_id=Str::getCompany($userId);
        $payment_receive->save();
        $payment_receive_id = $payment_receive->id;

        $payment_receive_details=json_decode($_POST['payment_receive_details']);

        $amount = 0;
        foreach ($payment_receive_details as $payment_item)
        {
            $amount += $payment_item->amountPaid;

            if ($amount <= $request->paidAmount)
            {
                $isPaid = true;
                $isPartialPaid = false;
                $totalAmount = $payment_item->amountPaid;
            }
            elseif($amount >= $request->paidAmount){
                $isPaid = false;
                $isPartialPaid = true;
                $totalAmount1 = $amount - $request->paidAmount;
                $totalAmount = $payment_item->amountPaid - $totalAmount1;
            }

            $data =  PaymentReceiveDetail::create([
                "amountPaid"        => $totalAmount,
                "sale_id"        => $payment_item->sale_id,
                "company_id" => Str::getCompany($userId),
                "user_id"      => $userId,
                "payment_receive_id"      => $payment_receive_id,
                'createdDate' => date('Y-m-d')
            ]);


            $sale = Sale::find($payment_item->sale_id);
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

        $Response = PaymentReceiveResource::collection(PaymentReceive::where('id',$payment_receive->id)->with('payment_receive_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        return array('customer'=>Customer::select('id','Name')->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'bank'=>Bank::select('id','Name')->orderBy('id','desc')->get());
    }

    public function customer_payments_push($Id)
    {
        $payment = PaymentReceive::with('customer')->find($Id);

        $user_id = Auth::id();
        $payment->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        if($payment->payment_type == 'cash')
        {
            $cash_transaction = new CashTransaction();
            $cash_transaction->Reference=$payment->id;
            $cash_transaction->createdDate=date('Y-m-d h:i:s');
            $cash_transaction->Type='Customer Payment';
            $cash_transaction->Credit=$payment->paidAmount;
            $cash_transaction->Debit=0.0;
            $cash_transaction->save();
        }

        ////////////////// account section ////////////////
        if ($payment)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $payment->customer_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalDebit = $payment->paidAmount;
                }
                else
                {
                    $totalDebit = $accountTransaction->Debit + $payment->paidAmount;
                }
                $difference = $accountTransaction->Differentiate - $payment->paidAmount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'customer_id'=> $payment->customer_id,
                    ])->get();
                $totalDebit = $payment->paidAmount;
                $difference = $accountTransaction->last()->Differentiate - $payment->paidAmount;
            }
            $AccData =
                [
                    'customer_id' => $payment->customer_id,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'customer_id'   => $payment->customer_id,
                ],
                $AccData);
        }
        ////////////////// end of account section ////////////////
        return TRUE;
    }
}
