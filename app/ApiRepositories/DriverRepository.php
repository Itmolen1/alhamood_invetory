<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\Http\Requests\BankRequest;
use App\Http\Requests\DriverRequest;
use App\Http\Resources\Driver\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverRepository implements IDriverRepositoryInterface
{
    public function all()
    {
        return DriverResource::collection(Driver::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return DriverResource::Collection(Driver::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $driver = new Driver();
        $driver->driverName=$request->driverName;
        $driver->Description=$request->Description;
        $driver->customer_id=$request->customer_id;
        $driver->company_id=$request->company_id;
        $driver->createdDate=date('Y-m-d h:i:s');
        $driver->isActive=1;
        $driver->user_id = $userId ?? 0;
        $driver->save();
        return new DriverResource(Driver::find($driver->id));
    }

    public function update(DriverRequest $driverRequest, $Id)
    {
        $userId = Auth::id();
        $driver = Driver::find($Id);
        $driverRequest['user_id']=$userId ?? 0;
        $driver->update($driverRequest->all());
        return new DriverResource(Driver::find($Id));
    }

    public function getById($Id)
    {
        return new DriverResource(Driver::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Driver::find($Id);
        $update->user_id=$userId;
        $update->save();
        $bank = Driver::withoutTrashed()->find($Id);
        if($bank->trashed())
        {
            return new DriverResource(Driver::onlyTrashed()->find($Id));
        }
        else
        {
            $bank->delete();
            return new DriverResource(Driver::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $driver = Driver::onlyTrashed()->find($Id);
        if (!is_null($driver))
        {
            $driver->restore();
            return new DriverResource(Driver::find($Id));
        }
        return new DriverResource(Driver::find($Id));
    }

    public function trashed()
    {
        $driver = Driver::onlyTrashed()->get();
        return DriverResource::collection($driver);
    }

    public function ActivateDeactivate($Id)
    {
        $driver = Driver::find($Id);
        if($driver->isActive==1)
        {
            $driver->isActive=0;
        }
        else
        {
            $driver->isActive=1;
        }
        $driver->update();
        return new DriverResource(Driver::find($Id));
    }
}
