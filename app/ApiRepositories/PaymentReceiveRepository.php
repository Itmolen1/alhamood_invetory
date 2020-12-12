<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use App\Http\Requests\PaymentReceiveRequest;
use App\Http\Resources\PaymentReceive\PaymentReceiveResource;
use App\Models\Bank;
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

        foreach ($payment_receive_details as $payment_item)
        {
            $data=PaymentReceiveDetail::create([
                'payment_receive_id'=>$payment_receive_id,
                'amountPaid'=>$payment_item->amountPaid,
                'sale_id'=>$payment_item->sale_id,
                'paymentReceiveDetailDate'=>date('Y-m-d'),
                'createdDate'=>date('Y-m-d'),
                'isActive'=>1,
                'user_id'=>$userId,
                'company_id'=>Str::getCompany($userId),
            ]);

            $sale = Sale::find($payment_item->sale_id);
            $sale->update([
                "paidBalance"        => $payment_item->amountPaid,
                "remainingBalance"   => 0,
                "IsPaid" => true,
                "IsPartialPaid" => false,
                "IsReturn" => false,
                "IsPartialReturn" => false,
                "IsNeedStampOrSignature" => false,
            ]);
        }

        $Response = PaymentReceiveResource::collection(PaymentReceive::where('id',$payment_receive->id)->with('payment_receive_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];

    }

    public function update(PaymentReceiveRequest $paymentReceiveRequest, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function ActivateDeactivate($Id)
    {
        // TODO: Implement ActivateDeactivate() method.
    }

    public function BaseList()
    {
        return array('customer'=>Customer::select('id','Name')->orderBy('id','desc')->get(),'payment_types'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'banks'=>Bank::select('id','Name')->orderBy('id','desc')->get());
    }
}
