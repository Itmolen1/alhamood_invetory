<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentReceiveRequest;
use App\MISC\ServiceResponse;
use App\Models\PaymentReceive;
use Illuminate\Http\Request;

class PaymentReceiveController extends Controller
{
    private $paymentReceiveRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IPaymentReceiveRepositoryInterface $paymentReceiveRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->paymentReceiveRepository=$paymentReceiveRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->paymentReceiveRepository->all());
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
            return $this->userResponse->Success($this->paymentReceiveRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $payment_receive=$this->paymentReceiveRepository->insert($request);
        return $this->userResponse->Success($payment_receive);
    }

    public function show($id)
    {
        try
        {
            $payment_receive = PaymentReceive::find($id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->paymentReceiveRepository->getById($id);
            return $this->userResponse->Success($payment_receive);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }

    }

    public function update(PaymentReceiveRequest $paymentReceiveRequest, $id)
    {
        try
        {
            $payment_receive = PaymentReceive::find($id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->paymentReceiveRepository->update($paymentReceiveRequest,$id);
            return $this->userResponse->Success($payment_receive);
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
            $payment_receive = PaymentReceive::find($Id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->paymentReceiveRepository->delete($request,$Id);
            return $this->userResponse->Success($payment_receive);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function customer_payments_push($Id)
    {
        try
        {
            $payment_receive = PaymentReceive::find($Id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->paymentReceiveRepository->customer_payments_push($Id);
            return $this->userResponse->Success($payment_receive);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = PaymentReceive::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->paymentReceiveRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function BaseList()
    {
        $data = $this->paymentReceiveRepository->BaseList();
        return $this->userResponse->Success($data);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $payment_receive = PaymentReceive::find($Id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $result=$this->paymentReceiveRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
