<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ISalesRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\MISC\ServiceResponse;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    private $salesRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ISalesRepositoryInterface $salesRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->salesRepository=$salesRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->salesRepository->all());
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
            return $this->userResponse->Success($this->salesRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        $sale_details=json_decode($_POST['sale_details']);
        if(isset($sale_details[0]->PadNumber))
        {
            $pad_number=$sale_details[0]->PadNumber;
        }
        else
        {
            $pad_number=0;
        }
        if($pad_number!=0)
        {
            $already_exist = SaleDetail::where('company_id',$company_id)->where('PadNumber',$pad_number)->first();
            if($already_exist)
            {
                return $this->userResponse->Failed($sales = (object)[],'PAD NUMBER ALREADY EXIST.');
            }
        }
        if($request->paidBalance > $request->grandTotal)
        {
            return $this->userResponse->Failed($sales = (object)[],'CAN NOT ENTER EXTRA CASH HERE GO TO ADVANCES.');
        }
        $sales=$this->salesRepository->insert($request);
        if($sales)
        {
            return $this->userResponse->Success($sales);
        }
        else
        {
            return $this->userResponse->Failed($sales = (object)[],'Something Went Wrong.');
        }
    }

    public function show($id)
    {
        try
        {
            $sales = Sale::find($id);
            if(is_null($sales))
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
            $sales = $this->salesRepository->getById($id);
            return $this->userResponse->Success($sales);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(Request $request)
    {
        try
        {
            $sales = Sale::find($request->id);
            if(is_null($sales))
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
            $sales = $this->salesRepository->update($request,$request->id);
            return $this->userResponse->Success($sales);
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
            $sales = Sale::find($Id);
            if(is_null($sales))
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
            $sales = $this->salesRepository->delete($request,$Id);
            return $this->userResponse->Success($sales);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Sale::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->salesRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function BaseList()
    {
        $data = $this->salesRepository->BaseList();
        return $this->userResponse->Success($data);
    }

    public function SalesDocumentsUpload(Request $request)
    {
        $this->salesRepository->SalesDocumentsUpload($request);
        return $this->userResponse->Success($purchase = (object)['message'=>'Document(s) uploaded.']);
    }

    public function print($id)
    {
        try
        {
            $sales = Sale::find($id);
            if(is_null($sales))
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
            $sales = $this->salesRepository->print($id);
            return $this->userResponse->Success($sales);
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
            $sales = Sale::find($Id);
            if(is_null($sales))
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
            $result=$this->salesRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function customerSaleDetails($Id)
    {
        try
        {
            $customer = Customer::find($Id);
            if(is_null($customer))
            {
                return $this->userResponse->Failed($customer = (object)[],'Customer Not Found.');
            }
            $result=$this->salesRepository->customerSaleDetails($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
