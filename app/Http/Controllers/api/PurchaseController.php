<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\MISC\ServiceResponse;
use App\Models\Purchase;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class PurchaseController extends Controller
{
    private $purchaseRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IPurchaseRepositoryInterface $purchaseRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->purchaseRepository=$purchaseRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->purchaseRepository->all());
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
            return $this->userResponse->Success($this->purchaseRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->purchaseRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $employee = Purchase::find($id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($employee);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(PurchaseRequest $purchaseRequest, $id)
    {
        try
        {
            $employee = Purchase::find($id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            return $this->purchaseRepository->update($purchaseRequest,$id);
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
            $employee = Purchase::find($Id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            $employee = $this->purchaseRepository->delete($request,$Id);
            return $this->userResponse->Success($employee);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Purchase::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->purchaseRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
