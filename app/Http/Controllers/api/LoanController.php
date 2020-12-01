<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ILoanRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest;
use App\MISC\ServiceResponse;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    private $loanRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ILoanRepositoryInterface $loanRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->loanRepository=$loanRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->loanRepository->all());
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
            return $this->userResponse->Success($this->loanRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->loanRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $loan = Loan::find($id);
            if(is_null($loan))
            {
                return $this->userResponse->Failed($loan = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($loan);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(LoanRequest $loanRequest, $id)
    {
        try
        {
            $loan = Loan::find($id);
            if(is_null($loan))
            {
                return $this->userResponse->Failed($loan = (object)[],'Not Found.');
            }
            $result = $this->loanRepository->update($loanRequest,$id);
            return $this->userResponse->Success($result);
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
            $loan = Loan::find($Id);
            if(is_null($loan))
            {
                return $this->userResponse->Failed($loan = (object)[],'Not Found.');
            }
            $loan = $this->loanRepository->delete($request,$Id);
            return $this->userResponse->Success($loan);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Loan::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->loanRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $loan = Loan::find($Id);
            if(is_null($loan))
            {
                return $this->userResponse->Failed($loan = (object)[],'Not Found.');
            }
            $result=$this->loanRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
