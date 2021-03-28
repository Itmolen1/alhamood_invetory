<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierAdvanceRequest;
use App\MISC\ServiceResponse;
use App\Models\SupplierAdvance;
use Illuminate\Http\Request;

class SupplierAdvanceController extends Controller
{
    private $supplierAdvanceRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ISupplierAdvanceRepositoryInterface $supplierAdvanceRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->supplierAdvanceRepository=$supplierAdvanceRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->supplierAdvanceRepository->all());
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
            return $this->userResponse->Success($this->supplierAdvanceRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function BaseList()
    {
        $data = $this->supplierAdvanceRepository->BaseList();
        return $this->userResponse->Success($data);
    }

    public function store(Request $request)
    {
        $result=$this->supplierAdvanceRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $supplier_advance = SupplierAdvance::find($id);
            if(is_null($supplier_advance))
            {
                return $this->userResponse->Failed($supplier_advance = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($supplier_advance);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function SupplierAdvanceUpdate(Request $request)
    {
        try
        {
            $id=$request->id;
            $supplier_advance = SupplierAdvance::find($id);
            if(is_null($supplier_advance))
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Not Found.');
            }
            if($supplier_advance->isPushed==1)
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Update is not allowed.');
            }
            $result=$this->supplierAdvanceRepository->update($request,$id);
            return $this->userResponse->Success($result);;
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
            $supplier_advance = SupplierAdvance::find($Id);
            if(is_null($supplier_advance))
            {
                return $this->userResponse->Failed($city = (object)[],'Not Found.');
            }
            $result = $this->supplierAdvanceRepository->delete($request,$Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = SupplierAdvance::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->supplierAdvanceRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $supplier_advance = SupplierAdvance::find($Id);
            if(is_null($supplier_advance))
            {
                return $this->userResponse->Failed($supplier_advance = (object)[],'Not Found.');
            }
            $result=$this->supplierAdvanceRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function supplier_advances_push($Id)
    {
        try
        {
            $supplier_advance = SupplierAdvance::find($Id);
            if(is_null($supplier_advance))
            {
                return $this->userResponse->Failed($supplier_advance = (object)[],'Not Found.');
            }
            if($supplier_advance->isPushed==1)
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Already Pushed.');
            }
            $supplier_advance = $this->supplierAdvanceRepository->supplier_advances_push($Id);
            return $this->userResponse->Success($supplier_advance);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
