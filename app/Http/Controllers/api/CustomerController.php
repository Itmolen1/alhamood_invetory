<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\Http\Controllers\Controller;
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try
        {
            $customer = Customer::create($request->all());
            return $this->userResponse->Success($customer);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try
        {
            $customer = Customer::find($id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Not Found.');
            }
            $customer->update($request->all());
            $customer->save();
            return $this->userResponse->Success($customer);
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
}
