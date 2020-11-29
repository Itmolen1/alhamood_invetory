<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\Http\Requests\CustomerAdvanceRequest;
use App\Http\Resources\CustomerAdvance\CustomerAdvanceResource;
use App\Models\CustomerAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAdvanceRepository implements ICustomerAdvanceRepositoryInterface
{
    public function all()
    {
        return CustomerAdvanceResource::collection(CustomerAdvance::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CustomerAdvanceResource::Collection(CustomerAdvance::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $customer_advance = new CustomerAdvance();
        $customer_advance->customer_id=$request->customer_id;
        $customer_advance->receiptNumber=$request->receiptNumber;
        $customer_advance->paymentType=$request->paymentType;
        $customer_advance->Amount=$request->Amount;
        $customer_advance->sumOf=$request->sumOf;
        $customer_advance->receiverName=$request->receiverName;
        $customer_advance->Description=$request->Description;
        $customer_advance->user_id=$request->user_id;
        $customer_advance->bank_id=$request->bank_id;
        $customer_advance->accountNumber=$request->accountNumber;
        $customer_advance->TransferDate=$request->TransferDate;
        $customer_advance->registerDate=$request->registerDate;
        $customer_advance->createdDate=date('Y-m-d h:i:s');
        $customer_advance->isActive=1;
        $customer_advance->user_id = $userId ?? 0;
        $customer_advance->save();
        return new CustomerAdvanceResource(CustomerAdvance::find($customer_advance->id));
    }

    public function update(CustomerAdvanceRequest $customerAdvanceRequest, $Id)
    {
        $userId = Auth::id();
        $customer_advance = CustomerAdvance::find($Id);
        $customerAdvanceRequest['user_id']=$userId ?? 0;
        $customer_advance->update($customerAdvanceRequest->all());
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function getById($Id)
    {
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = CustomerAdvance::find($Id);
        $update->user_id=$userId;
        $update->save();
        $customer_advance = CustomerAdvance::withoutTrashed()->find($Id);
        if($customer_advance->trashed())
        {
            return new CustomerAdvanceResource(CustomerAdvance::onlyTrashed()->find($Id));
        }
        else
        {
            $customer_advance->delete();
            return new CustomerAdvanceResource(CustomerAdvance::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $customer_advance = CustomerAdvance::onlyTrashed()->find($Id);
        if (!is_null($customer_advance))
        {
            $customer_advance->restore();
            return new CustomerAdvanceResource(CustomerAdvance::find($Id));
        }
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function trashed()
    {
        $customer_advance = CustomerAdvance::onlyTrashed()->get();
        return CustomerAdvanceResource::collection($customer_advance);
    }
}
