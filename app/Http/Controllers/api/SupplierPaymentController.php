<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private $supplierPaymentRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ISupplierPaymentRepositoryInterface $supplierPaymentRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->supplierPaymentRepository=$supplierPaymentRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->supplierPaymentRepository->all());
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
            return $this->userResponse->Success($this->supplierPaymentRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $payment_receive=$this->supplierPaymentRepository->insert($request);
        return $this->userResponse->Success($payment_receive);
    }

    public function show($id)
    {
        try
        {
            $payment_receive = SupplierPayment::find($id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->supplierPaymentRepository->getById($id);
            return $this->userResponse->Success($payment_receive);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }

    }

    public function supplier_payments_push($Id)
    {
        try
        {
            $payment_receive = SupplierPayment::find($Id);
            if(is_null($payment_receive))
            {
                return $this->userResponse->Failed($payment_receive = (object)[],'Not Found.');
            }
            $payment_receive = $this->supplierPaymentRepository->supplier_payments_push($Id);
            return $this->userResponse->Success($payment_receive);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function BaseList()
    {
        $data = $this->supplierPaymentRepository->BaseList();
        return $this->userResponse->Success($data);
    }
}
