<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\Http\Requests\BankRequest;
use App\Http\Resources\Driver\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;

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
        $driver = new Driver();
        $driver->driverName=$request->driverName;
        $driver->Description=$request->Description;
        $driver->customer_id=$request->customer_id;
        $driver->company_id=$request->company_id;
        $driver->createdDate=date('Y-m-d h:i:s');
        $driver->isActive=1;
        $driver->user_id = 1;//login user id
        $driver->save();
        return new DriverResource(Driver::find($driver->Id));
    }

    public function update(BankRequest $bankRequest, $Id)
    {
        $driver = Driver::find($Id);
        $driver->update($bankRequest->all());
        return new DriverResource(Driver::find($Id));
    }

    public function getById($Id)
    {
        return new DriverResource(Driver::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $update = Driver::find($Id);
        $update->update($request->all());
        $driver = Driver::withoutTrashed()->find($Id);
        if($driver->trashed())
        {
            return new DriverResource(Driver::onlyTrashed()->find($Id));
        }
        else
        {
            $driver->delete();
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
}
