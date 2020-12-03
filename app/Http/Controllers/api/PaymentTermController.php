<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IPaymentTermRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTermRequest;
use App\MISC\ServiceResponse;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    private $paymentTermRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IPaymentTermRepositoryInterface $paymentTermRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->paymentTermRepository=$paymentTermRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->paymentTermRepository->all());
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
            return $this->userResponse->Success($this->paymentTermRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->paymentTermRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $payment_term = PaymentTerm::find($id);
            if(is_null($payment_term))
            {
                return $this->userResponse->Failed($payment_term = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($payment_term);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(PaymentTermRequest $paymentTermRequest, $id)
    {
        try
        {
            $payment_term = PaymentTerm::find($id);
            if(is_null($payment_term))
            {
                return $this->userResponse->Failed($payment_term = (object)[],'Not Found.');
            }
            return $this->paymentTermRepository->update($paymentTermRequest,$id);
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
            $payment_term = PaymentTerm::find($Id);
            if(is_null($payment_term))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            $payment_term = $this->paymentTermRepository->delete($request,$Id);
            return $this->userResponse->Success($payment_term);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = PaymentTerm::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->paymentTermRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $payment_term = PaymentTerm::find($Id);
            if(is_null($payment_term))
            {
                return $this->userResponse->Failed($payment_term = (object)[],'Not Found.');
            }
            $result=$this->paymentTermRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
