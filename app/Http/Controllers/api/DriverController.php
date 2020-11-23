<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use App\Models\Driver;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class DriverController extends Controller
{
    private $driverRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IDriverRepositoryInterface $driverRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->driverRepository=$driverRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->driverRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->driverRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try
        {
            $driver = Driver::create($request->all());
            return $this->userResponse->Success($driver);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function show($id)
    {
        try
        {
            $driver = Driver::find($id);
            if(is_null($driver))
            {
                return $this->userResponse->Failed($driver = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($driver);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try
        {
            $driver = Driver::find($id);
            if(is_null($driver))
            {
                return $this->userResponse->Failed($driver = (object)[],'Not Found.');
            }
            $driver->update($request->all());
            $driver->save();
            return $this->userResponse->Success($driver);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $driver = $this->driverRepository->delete($request,$Id);
            return $this->userResponse->Success($driver);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Driver::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->driverRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
