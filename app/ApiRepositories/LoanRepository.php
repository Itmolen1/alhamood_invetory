<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ILoanRepositoryInterface;
use App\Http\Requests\LoanRequest;
use App\Http\Resources\Loan\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoanRepository implements ILoanRepositoryInterface
{

    public function all()
    {
        return LoanResource::collection(Loan::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return LoanResource::Collection(Loan::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $loan = new Loan();
        $loan->loanTo=$request->loanTo;
        $loan->payLoan=$request->payLoan;
        $loan->loanInWords=$request->loanInWords;
        $loan->voucherNumber=$request->voucherNumber;
        $loan->remainingLoan=$request->remainingLoan;
        $loan->user_id=$request->user_id;
        $loan->customer_id=$request->customer_id;
        $loan->employee_id=$request->employee_id;
        $loan->Description=$request->Description;
        $loan->loanDate=$request->loanDate;
        $loan->isPay=$request->isPay;
        $loan->isReturn=$request->isReturn;
        $loan->createdDate=date('Y-m-d h:i:s');
        $loan->isActive=1;
        $loan->user_id = $userId ?? 0;
        $loan->company_id=Str::getCompany($userId);
        $loan->save();
        return new LoanResource(Loan::find($loan->id));
    }

    public function update(LoanRequest $loanRequest, $Id)
    {
        $userId = Auth::id();
        $loan = Loan::find($Id);
        $loanRequest['user_id']=$userId ?? 0;
        $loan->update($loanRequest->all());
        return new LoanResource(Loan::find($Id));
    }

    public function getById($Id)
    {
        return new LoanResource(Loan::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Loan::find($Id);
        $update->user_id=$userId;
        $update->save();
        $loan = Loan::withoutTrashed()->find($Id);
        if($loan->trashed())
        {
            return new LoanResource(Loan::onlyTrashed()->find($Id));
        }
        else
        {
            $loan->delete();
            return new LoanResource(Loan::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $loan = Loan::onlyTrashed()->find($Id);
        if (!is_null($loan))
        {
            $loan->restore();
            return new LoanResource(Loan::find($Id));
        }
        return new LoanResource(Loan::find($Id));
    }

    public function trashed()
    {
        $loan = Loan::onlyTrashed()->get();
        return LoanResource::collection($loan);
    }

    public function ActivateDeactivate($Id)
    {
        $loan = Loan::find($Id);
        if($loan->isActive==1)
        {
            $loan->isActive=0;
        }
        else
        {
            $loan->isActive=1;
        }
        $loan->update();
        return new LoanResource(Loan::find($Id));
    }
}
