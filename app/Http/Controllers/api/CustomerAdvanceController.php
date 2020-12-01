<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAdvanceRequest;
use App\MISC\ServiceResponse;
use App\Models\CustomerAdvance;
use Illuminate\Http\Request;

class CustomerAdvanceController extends Controller
{
    private $customerAdvanceRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ICustomerAdvanceRepositoryInterface $customerAdvanceRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->customerAdvanceRepository=$customerAdvanceRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->customerAdvanceRepository->all());
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
            return $this->userResponse->Success($this->customerAdvanceRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $result=$this->customerAdvanceRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $customer_advance = CustomerAdvance::find($id);
            if(is_null($customer_advance))
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($customer_advance);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CustomerAdvanceRequest $customerAdvanceRequest, $id)
    {
        try
        {
            $customer_advance = CustomerAdvance::find($id);
            if(is_null($customer_advance))
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Not Found.');
            }
            $result=$this->customerAdvanceRepository->update($customerAdvanceRequest,$id);
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
            $customer_advance = CustomerAdvance::find($Id);
            if(is_null($customer_advance))
            {
                return $this->userResponse->Failed($city = (object)[],'Not Found.');
            }
            $result = $this->customerAdvanceRepository->delete($request,$Id);
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
            $restore = CustomerAdvance::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->customerAdvanceRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $customer_advance = CustomerAdvance::find($Id);
            if(is_null($customer_advance))
            {
                return $this->userResponse->Failed($customer_advance = (object)[],'Not Found.');
            }
            $result=$this->customerAdvanceRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
