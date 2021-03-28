<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\Http\Requests\SupplierAdvanceRequest;
use App\Http\Resources\SupplierAdvance\SupplierAdvanceResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\PaymentType;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierAdvanceRepository implements ISupplierAdvanceRepositoryInterface
{
    public function all()
    {
        return SupplierAdvanceResource::collection(SupplierAdvance::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        return SupplierAdvanceResource::Collection(SupplierAdvance::with('api_supplier')->where('company_id', $company_id)->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        return array('supplier'=>Supplier::select('id','Name')->where('company_id', $company_id)->where('company_type_id',2)->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'bank'=>Bank::select('id','Name', 'Description')->orderBy('id','desc')->get());
    }

    public function insert(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $advance = [
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->Amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$request->Amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>Str::getUAECurrency($request->Amount),
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber,
            'ChequeNumber' =>$request->ChequeNumber,
            'TransferDate' =>$request->TransferDate,
            'registerDate' =>$request->registerDate,
            'bank_id' =>$request->bank_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'supplier_id' =>$request->supplier_id ?? 0,
            'Description' =>$request->Description,
        ];
        $supplier_advance=SupplierAdvance::create($advance);
        return new SupplierAdvanceResource(SupplierAdvance::find($supplier_advance->id));
    }

    public function update(Request $request, $Id)
    {
        $user_id = Auth::id();
        $advance = SupplierAdvance::find($Id);
        $advance->update([
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->Amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$request->Amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>Str::getUAECurrency($request->Amount),
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber,
            'ChequeNumber' =>$request->ChequeNumber ?? 0,
            'TransferDate' =>$request->TransferDate,
            'registerDate' =>$request->registerDate,
            'bank_id' =>$request->bank_id ?? 0,
            'user_id' =>$user_id,
            'supplier_id' =>$request->supplier_id ?? 0,
            'Description' =>$request->Description,
        ]);
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function getById($Id)
    {
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = SupplierAdvance::find($Id);
        $update->user_id=$userId;
        $update->save();
        $supplier_advance = SupplierAdvance::withoutTrashed()->find($Id);
        if($supplier_advance->trashed())
        {
            return new SupplierAdvanceResource(SupplierAdvance::onlyTrashed()->find($Id));
        }
        else
        {
            $supplier_advance->delete();
            return new SupplierAdvanceResource(SupplierAdvance::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier_advance = SupplierAdvance::onlyTrashed()->find($Id);
        if (!is_null($supplier_advance))
        {
            $supplier_advance->restore();
            return new SupplierAdvanceResource(SupplierAdvance::find($Id));
        }
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function trashed()
    {
        $supplier_advance = SupplierAdvance::onlyTrashed()->get();
        return SupplierAdvanceResource::collection($supplier_advance);
    }

    public function ActivateDeactivate($Id)
    {
        $supplier_advance = SupplierAdvance::find($Id);
        if($supplier_advance->isActive==1)
        {
            $supplier_advance->isActive=0;
        }
        else
        {
            $supplier_advance->isActive=1;
        }
        $supplier_advance->update();
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function supplier_advances_push($Id)
    {
        DB::transaction(function () use($Id) {
            $user_id = Auth::id();
            $company_id = Str::getCompany($user_id);

            $advance = SupplierAdvance::with('supplier')->find($Id);

            if ($advance->Amount > 0) {
                $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                $closing_before_advance_debit = $accountTransaction->last()->Differentiate;

                $accountTransaction_ref = 0;
                // account section by gautam //
                if ($advance->paymentType == 'cash') {
                    $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $Id;
                    $cash_transaction->createdDate = $advance->TransferDate;
                    $cash_transaction->Type = 'supplier_advances';
                    $cash_transaction->Details = 'SupplierCashAdvance|' . $Id;
                    $cash_transaction->Credit = $advance->Amount;
                    $cash_transaction->Debit = 0.00;
                    $cash_transaction->Differentiate = $difference - $advance->Amount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierCashAdvance|' . $Id,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                } elseif ($advance->paymentType == 'bank') {
                    $bankTransaction = BankTransaction::where(['bank_id' => $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference = $Id;
                    $bank_transaction->createdDate = $advance->TransferDate;
                    $bank_transaction->Type = 'supplier_advances';
                    $bank_transaction->Details = 'SupplierBankAdvance|' . $Id;
                    $bank_transaction->Credit = $advance->Amount;
                    $bank_transaction->Debit = 0.00;
                    $bank_transaction->Differentiate = $difference - $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierBankAdvance|' . $Id,
                            'referenceNumber' => $advance->ChequeNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                } elseif ($advance->paymentType == 'cheque') {
                    $bankTransaction = BankTransaction::where(['bank_id' => $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference = $Id;
                    $bank_transaction->createdDate = $advance->TransferDate;
                    $bank_transaction->Type = 'supplier_advances';
                    $bank_transaction->Details = 'SupplierChequeAdvance|' . $Id;
                    $bank_transaction->Credit = $advance->Amount;
                    $bank_transaction->Debit = 0.00;
                    $bank_transaction->Differentiate = $difference - $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierChequeAdvance|' . $Id,
                            'referenceNumber' => $advance->ChequeNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                }
                // account section by gautam //
            }
            $advance->update([
                'isPushed' => true,
                'user_id' => $user_id,
            ]);
        });
        return TRUE;
    }
}
