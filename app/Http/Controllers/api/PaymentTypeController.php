<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTypeRequest;
use App\MISC\ServiceResponse;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    private $paymentTypeRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IPaymentTypeRepositoryInterface $paymentTypeRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->paymentTypeRepository=$paymentTypeRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->paymentTypeRepository->all());
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
            return $this->userResponse->Success($this->paymentTypeRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->paymentTypeRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $company_type = PaymentType::find($id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($company_type);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(PaymentTypeRequest $paymentTypeRequest, $id)
    {
        try
        {
            $company_type = PaymentType::find($id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            return $this->paymentTypeRepository->update($paymentTypeRequest,$id);
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
            $company_type = PaymentType::find($Id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            $company_type = $this->paymentTypeRepository->delete($request,$Id);
            return $this->userResponse->Success($company_type);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = PaymentType::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->paymentTypeRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $company_type = PaymentType::find($Id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            $result=$this->paymentTypeRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
