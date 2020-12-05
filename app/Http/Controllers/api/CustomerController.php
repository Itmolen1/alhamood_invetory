<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\MISC\ServiceResponse;
use App\Models\Customer;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class CustomerController extends Controller
{
    private $customerRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ICustomerRepositoryInterface $customerRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->customerRepository=$customerRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->customerRepository->all());
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
            return $this->userResponse->Success($this->customerRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->customerRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $customer = Customer::find($id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($customer);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CustomerRequest $customerRequest, $id)
    {
        try
        {
            $customer = Customer::find($id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Not Found.');
            }
            return $this->customerRepository->update($customerRequest,$id);
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
            $customer = Customer::find($Id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Not Found.');
            }
            $customer = $this->customerRepository->delete($request,$Id);
            return $this->userResponse->Success($customer);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Customer::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->customerRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $customer = Customer::find($Id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Not Found.');
            }
            $result=$this->customerRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function BaseList()
    {
        $data = $this->customerRepository->BaseList();
        return $this->userResponse->Success($data);
    }
}
