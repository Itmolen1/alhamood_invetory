<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\MISC\ServiceResponse;
use App\Models\Supplier;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class SupplierController extends Controller
{
    private $supplierRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ISupplierRepositoryInterface $supplierRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->supplierRepository=$supplierRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->supplierRepository->all());
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
            return $this->userResponse->Success($this->supplierRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->supplierRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $supplier = Supplier::find($id);
            if(is_null($supplier))
            {
                return $this->userResponse->Failed($supplier = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($supplier);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(SupplierRequest $supplierRequest, $id)
    {
        try
        {
            $supplier = Supplier::find($id);
            if(is_null($supplier))
            {
                return $this->userResponse->Failed($supplier = (object)[],'Not Found.');
            }
            return $this->supplierRepository->update($supplierRequest,$id);
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
            $supplier = Supplier::find($Id);
            if(is_null($supplier))
            {
                return $this->userResponse->Failed($supplier = (object)[],'Not Found.');
            }
            $supplier = $this->supplierRepository->delete($request,$Id);
            return $this->userResponse->Success($supplier);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Supplier::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->supplierRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $supplier = Supplier::find($Id);
            if(is_null($supplier))
            {
                return $this->userResponse->Failed($supplier = (object)[],'Not Found.');
            }
            $result=$this->supplierRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function BaseList()
    {
        $data = $this->supplierRepository->BaseList();
        return $this->userResponse->Success($data);
    }
}
