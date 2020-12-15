<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\MISC\ServiceResponse;
use App\Models\Purchase;
use App\Models\Supplier;
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
        $purchase=$this->purchaseRepository->insert($request);
        return $this->userResponse->Success($purchase);
    }

    public function show($id)
    {
        try
        {
            $purchase = Purchase::find($id);
            if(is_null($purchase))
            {
                return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
            }
            $purchase = $this->purchaseRepository->getById($id);
            return $this->userResponse->Success($purchase);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }

    }

    public function update(PurchaseRequest $purchaseRequest)
    {
        try
        {
            $employee = Purchase::find($purchaseRequest->id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            $purchase = $this->purchaseRepository->update($purchaseRequest,$purchaseRequest->id);
            return $this->userResponse->Success($purchase);
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
            $purchase = Purchase::find($Id);
            if(is_null($purchase))
            {
                return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
            }
            $purchase = $this->purchaseRepository->delete($request,$Id);
            return $this->userResponse->Success($purchase);
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

    public function BaseList()
    {
        $data = $this->purchaseRepository->BaseList();
        return $this->userResponse->Success($data);
    }

    public function PurchaseDocumentsUpload(Request $request)
    {
        $this->purchaseRepository->PurchaseDocumentsUpload($request);
        return $this->userResponse->Success($purchase = (object)['message'=>'Document(s) uploaded.']);
    }

    public function print($id)
    {
        try
        {
            $purchase = Purchase::find($id);
            if(is_null($purchase))
            {
                return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
            }
            $purchase = $this->purchaseRepository->print($id);
            return $this->userResponse->Success($purchase);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $purchase = Purchase::find($Id);
            if(is_null($purchase))
            {
                return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
            }
            $result=$this->purchaseRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function supplierPurchaseDetails($Id)
    {
        try
        {
            $supplier = Supplier::find($Id);
            if(is_null($supplier))
            {
                return $this->userResponse->Failed($supplier = (object)[],'Supplier Not Found.');
            }
            $result=$this->purchaseRepository->supplierPurchaseDetails($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
