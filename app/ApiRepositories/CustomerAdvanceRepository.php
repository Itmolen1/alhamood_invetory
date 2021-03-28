<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\Http\Requests\CustomerAdvanceRequest;
use App\Http\Resources\CustomerAdvance\CustomerAdvanceResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerAdvanceRepository implements ICustomerAdvanceRepositoryInterface
{
    public function all()
    {
        return CustomerAdvanceResource::collection(CustomerAdvance::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        return CustomerAdvanceResource::Collection(CustomerAdvance::with('api_customer')->where('company_id', $company_id)->get()->sortDesc()->forPage($page_no, $page_size));
    }

    public function insert(Request $request)
    {
        $advance_id = 0;
        DB::transaction(function () use ($request, &$advance_id) {
            $user_id = Auth::id();
            $company_id = Str::getCompany($user_id);

            $advance = [
                'receiptNumber' => $request->receiptNumber,
                'paymentType' => $request->paymentType,
                'Amount' => $request->Amount,
                'spentBalance' => 0.00,
                'remainingBalance' => $request->Amount,
                'IsSpent' => 0,
                'IsPartialSpent' => 0,
                'sumOf' => Str::getUAECurrency($request->Amount),
                'Description' => $request->Description,
                'receiverName' => $request->receiverName,
                'accountNumber' => $request->accountNumber ?? 0,
                'ChequeNumber' => $request->ChequeNumber,
                'TransferDate' => $request->TransferDate ?? 0,
                'registerDate' => $request->registerDate,
                'bank_id' => $request->bank_id ?? 0,
                'user_id' => $user_id,
                'company_id' => $company_id,
                'customer_id' => $request->customer_id ?? 0,
            ];
            $advance = CustomerAdvance::create($advance);
            $advance_id = $advance->id;
        });
        return new CustomerAdvanceResource(CustomerAdvance::find($advance_id));
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use ($request, $Id) {
            $advance = CustomerAdvance::find($Id);

            $user_id = Auth::id();
            $advance->update([
                'receiptNumber' => $request->receiptNumber,
                'paymentType' => $request->paymentType,
                'Amount' => $request->amount,
                'spentBalance' => 0.00,
                'remainingBalance' => $request->amount,
                'IsSpent' => 0,
                'IsPartialSpent' => 0,
                'sumOf' => $request->amountInWords,
                'receiverName' => $request->receiverName,
                'accountNumber' => $request->accountNumber ?? null,
                'ChequeNumber' => $request->ChequeNumber ?? 0,
                'TransferDate' => $request->TransferDate,
                'registerDate' => $request->registerDate,
                'bank_id' => $request->bank_id ?? 0,
                'user_id' => $user_id,
                'customer_id' => $request->customer_id ?? null,
                'Description' => $request->Description,
            ]);
        });
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function getById($Id)
    {
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        return array('customer' => Customer::select('id', 'Name')->where('company_id', $company_id)->orderBy('id', 'desc')->get(), 'payment_type' => PaymentType::select('id', 'Name')->orderBy('id', 'desc')->get(), 'bank' => Bank::select('id', 'Name', 'Description')->orderBy('id', 'desc')->get());
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id'] = $userId ?? 0;
        $update = CustomerAdvance::find($Id);
        $update->user_id = $userId;
        $update->save();
        $customer_advance = CustomerAdvance::withoutTrashed()->find($Id);
        if ($customer_advance->trashed()) {
            return new CustomerAdvanceResource(CustomerAdvance::onlyTrashed()->find($Id));
        } else {
            $customer_advance->delete();
            return new CustomerAdvanceResource(CustomerAdvance::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $customer_advance = CustomerAdvance::onlyTrashed()->find($Id);
        if (!is_null($customer_advance)) {
            $customer_advance->restore();
            return new CustomerAdvanceResource(CustomerAdvance::find($Id));
        }
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function trashed()
    {
        $customer_advance = CustomerAdvance::onlyTrashed()->get();
        return CustomerAdvanceResource::collection($customer_advance);
    }

    public function ActivateDeactivate($Id)
    {
        $customer_advance = CustomerAdvance::find($Id);
        if ($customer_advance->isActive == 1) {
            $customer_advance->isActive = 0;
        } else {
            $customer_advance->isActive = 1;
        }
        $customer_advance->update();
        return new CustomerAdvanceResource(CustomerAdvance::find($Id));
    }

    public function customer_advances_push($Id)
    {
        /* start of new code */
        DB::transaction(function () use ($Id) {
            $advance = CustomerAdvance::with('customer')->find($Id);

            $user_id = Auth::id();
            $company_id = Str::getCompany($user_id);

            if ($advance->Amount > 0) {
                $accountTransaction = AccountTransaction::where(['customer_id' => $advance->customer_id,])->get();
                $closing_before_advance_credit = $accountTransaction->last()->Differentiate;

                $accountTransaction_ref = 0;
                // account section by gautam //
                if ($advance->paymentType == 'cash') {
                    $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $Id;
                    $cash_transaction->createdDate = $advance->TransferDate;
                    $cash_transaction->Type = 'customer_advances';
                    $cash_transaction->Details = 'CustomerCashAdvance|' . $Id;
                    $cash_transaction->Credit = 0.00;
                    $cash_transaction->Debit = $advance->Amount;
                    $cash_transaction->Differentiate = $difference + $advance->Amount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id' => $advance->customer_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'CustomerCashAdvance|' . $Id,
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
                    $bank_transaction->Type = 'customer_advances';
                    $bank_transaction->Details = 'CustomerBankAdvance|' . $Id;
                    $bank_transaction->Credit = 0.00;
                    $bank_transaction->Debit = $advance->Amount;
                    $bank_transaction->Differentiate = $difference + $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id' => $advance->customer_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'CustomerBankAdvance|' . $Id,
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
                    $bank_transaction->Type = 'customer_advances';
                    $bank_transaction->Details = 'CustomerChequeAdvance|' . $Id;
                    $bank_transaction->Credit = 0.00;
                    $bank_transaction->Debit = $advance->Amount;
                    $bank_transaction->Differentiate = $difference + $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id' => $advance->customer_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
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
        /* end of new code */
        return TRUE;
    }
}
