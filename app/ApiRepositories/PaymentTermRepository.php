<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPaymentTermRepositoryInterface;
use App\Http\Requests\PaymentTermRequest;
use App\Http\Resources\PaymentTerm\PaymentTermResource;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentTermRepository implements IPaymentTermRepositoryInterface
{
    public function all()
    {
        return PaymentTermResource::collection(PaymentTerm::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return PaymentTermResource::Collection(PaymentTerm::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $payment_term = new PaymentTerm();
        $payment_term->Name=$request->Name;
        $payment_term->Description=$request->Description;
        $payment_term->createdDate=date('Y-m-d h:i:s');
        $payment_term->isActive=1;
        $payment_term->user_id = $userId ?? 0;
        $payment_term->company_id=Str::getCompany($userId);
        $payment_term->save();
        return new PaymentTermResource(PaymentTerm::find($payment_term->id));
    }

    public function update(PaymentTermRequest $paymentTermRequest, $Id)
    {
        $userId = Auth::id();
        $payment_term = PaymentTerm::find($Id);
        $paymentTermRequest['user_id']=$userId ?? 0;
        $payment_term->update($paymentTermRequest->all());
        return new PaymentTermResource(PaymentTerm::find($Id));
    }

    public function getById($Id)
    {
        return new PaymentTermResource(PaymentTerm::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = PaymentTerm::find($Id);
        $update->user_id=$userId;
        $update->save();
        $payment_term = PaymentTerm::withoutTrashed()->find($Id);
        if($payment_term->trashed())
        {
            return new PaymentTermResource(PaymentTerm::onlyTrashed()->find($Id));
        }
        else
        {
            $payment_term->delete();
            return new PaymentTermResource(PaymentTerm::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $payment_term = PaymentTerm::onlyTrashed()->find($Id);
        if (!is_null($payment_term))
        {
            $payment_term->restore();
            return new PaymentTermResource(PaymentTerm::find($Id));
        }
        return new PaymentTermResource(PaymentTerm::find($Id));
    }

    public function trashed()
    {
        $payment_term = PaymentTerm::onlyTrashed()->get();
        return PaymentTermResource::collection($payment_term);
    }

    public function ActivateDeactivate($Id)
    {
        $payment_term = PaymentTerm::find($Id);
        if($payment_term->isActive==1)
        {
            $payment_term->isActive=0;
        }
        else
        {
            $payment_term->isActive=1;
        }
        $payment_term->update();
        return new PaymentTermResource(PaymentTerm::find($Id));
    }
}
