<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankRequest;
use App\MISC\ServiceResponse;
use App\Models\Bank;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class BankController extends Controller
{
    private $userResponse;
    private $bankRepository;

    public function __construct(ServiceResponse $serviceResponse, IBankRepositoryInterface $bankRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->bankRepository=$bankRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->bankRepository->all());
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
            return $this->userResponse->Success($this->bankRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(BankRequest $bankRequest)
    {
        return $this->bankRepository->insert($bankRequest);
    }

    public function show($id)
    {
        try
        {
            $bank = Bank::find($id);
            if(is_null($bank))
            {
                return $this->userResponse->Failed($bank = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($bank);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(Request $request,$id)
    {
        try
        {
            $bank = Bank::find($id);
            if(is_null($bank))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            return $this->bankRepository->update($request,$id);
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
            $bank = Bank::find($Id);
            if(is_null($bank))
            {
                return $this->userResponse->Failed($bank = (object)[],'Not Found.');
            }
            $bank = $this->bankRepository->delete($request,$Id);
            return $this->userResponse->Success($bank);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Bank::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->bankRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $bank = Bank::find($Id);
            if(is_null($bank))
            {
                return $this->userResponse->Failed($bank = (object)[],'Not Found.');
            }
            $result=$this->bankRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
