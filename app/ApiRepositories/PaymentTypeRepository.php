<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use App\Http\Requests\PaymentTypeRequest;
use App\Http\Resources\PaymentType\PaymentTypeResource;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentTypeRepository implements IPaymentTypeRepositoryInterface
{
    public function all()
    {
        return PaymentTypeResource::collection(PaymentType::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return PaymentTypeResource::Collection(PaymentType::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(PaymentTypeRequest $paymentTypeRequest)
    {
        $userId = Auth::id();
        $payment_type = new PaymentType();
        $payment_type->Name=$paymentTypeRequest->Name;
        $payment_type->Description=$paymentTypeRequest->Description;
        $payment_type->createdDate=date('Y-m-d h:i:s');
        $payment_type->isActive=1;
        $payment_type->user_id = $userId ?? 0;
        $payment_type->save();
        return new PaymentTypeResource(PaymentType::find($payment_type->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $payment_type = PaymentType::find($Id);
        $request['user_id']=$userId ?? 0;
        $payment_type->update($request->all());
        return new PaymentTypeResource(PaymentType::find($Id));
    }

    public function getById($Id)
    {
        return new PaymentTypeResource(PaymentType::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = PaymentType::find($Id);
        $update->user_id=$userId;
        $update->save();
        $payment_type = PaymentType::withoutTrashed()->find($Id);
        if($payment_type->trashed())
        {
            return new PaymentTypeResource(PaymentType::onlyTrashed()->find($Id));
        }
        else
        {
            $payment_type->delete();
            return new PaymentTypeResource(PaymentType::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $payment_type = PaymentType::onlyTrashed()->find($Id);
        if (!is_null($payment_type))
        {
            $payment_type->restore();
            return new PaymentTypeResource(PaymentType::find($Id));
        }
        return new PaymentTypeResource(PaymentType::find($Id));
    }

    public function trashed()
    {
        $payment_type = PaymentType::onlyTrashed()->get();
        return PaymentTypeResource::collection($payment_type);
    }

    public function ActivateDeactivate($Id)
    {
        $payment_type = PaymentType::find($Id);
        if($payment_type->isActive==1)
        {
            $payment_type->isActive=0;
        }
        else
        {
            $payment_type->isActive=1;
        }
        $payment_type->update();
        return new PaymentTypeResource(PaymentType::find($Id));
    }
}
