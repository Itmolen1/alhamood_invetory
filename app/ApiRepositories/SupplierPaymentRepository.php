<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use App\Http\Resources\SupplierPayment\SupplierPaymentResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\PaymentType;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierPaymentRepository implements ISupplierPaymentRepositoryInterface
{

    public function all()
    {
        return SupplierPaymentResource::collection(SupplierPayment::with('user')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return SupplierPaymentResource::Collection(SupplierPayment::with('supplier_payment_details')->where('company_id',$company_id)->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        /* start of new code */
        $payment = new SupplierPayment();
        $payment->supplier_id = $request->supplier_id;
        $payment->totalAmount = $request->totalAmount;
        $payment->payment_type = $request->payment_type;
        $payment->referenceNumber = $request->referenceNumber;
        $payment->supplierPaymentDate = $request->supplierPaymentDate;
        $payment->paidAmount = $request->paidAmount;
        $payment->amountInWords = Str::getUAECurrency($request->paidAmount);
        $payment->receiptNumber = $request->receiptNumber;
        $payment->receiverName = $request->receiverName;
        $payment->transferDate = $request->transferDate;
        $payment->accountNumber = $request->accountNumber;
        $payment->Description = $request->Description;
        $payment->bank_id = $request->bank_id ?? 0;
        $payment->user_id = $user_id;
        $payment->createdDate = $request->supplierPaymentDate;
        $payment->company_id = $company_id;
        $payment->save();
        $payment = $payment->id;

        $total_i_have=$request->paidAmount;
        $supplier_payment_details=json_decode($_POST['supplier_payment_details']);
        foreach($supplier_payment_details as $detail)
        {
            $this_purchase=Purchase::where('id',$detail->purchase_id)->get()->first();
            if($this_purchase->IsPaid==0 AND $this_purchase->remainingBalance!=0)
            {
                $total_you_need = $this_purchase->remainingBalance;
                $still_payable_to_you=0;
                $total_giving_to_you=0;
                $isPartialPaid = 0;
                if ($total_i_have >= $total_you_need)
                {
                    $total_i_have = $total_i_have - $total_you_need;
                    $total_giving_to_you=$total_you_need;
                }
                else
                {
                    $total_giving_to_you=$total_i_have;
                    $total_i_have = $total_i_have - $total_giving_to_you;
                }
                SupplierPaymentDetail::create([
                    "amountPaid" => $total_giving_to_you,
                    "purchase_id" => $detail->purchase_id,
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "supplier_payment_id" => $payment,
                    'createdDate' => $request->supplierPaymentDate,
                ]);
                if($total_i_have<=0)
                {
                    break;
                }
            }
        }
        /* end of new code */
        $Response = SupplierPaymentResource::collection(SupplierPayment::where('id',$payment)->with('supplier_payment_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function supplier_payments_push($Id)
    {
//        DB::transaction(function () use($Id) {
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);

            $payments = SupplierPayment::with('supplier', 'supplier_payment_details')->find($Id);

            foreach ($payments->supplier_payment_details as $single) {
                $purchase = Purchase::where('id', $single->purchase_id)->get()->first();
                $is_paid = 0;
                if ($purchase->remainingBalance - $single->amountPaid == 0) {
                    $is_paid = 1;
                    $is_partial_paid = 0;
                } else {
                    $is_partial_paid = 1;
                }
                $purchase->update([
                    'paidBalance' => $purchase->paidBalance + $single->amountPaid,
                    'remainingBalance' => $purchase->remainingBalance - $single->amountPaid,
                    'Description' => $payments->referenceNumber,
                    'IsPaid' => $is_paid,
                    'IsPartialPaid' => $is_partial_paid,
                ]);
            }
            $payments->update([
                'isPushed' => true,
                'user_id' => $user_id,
            ]);

            $accountTransaction_ref = 0;

            if ($payments->payment_type == 'cash') {
                $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference = $Id;
                $cash_transaction->createdDate = $payments->transferDate ?? date('Y-m-d h:i:s');
                $cash_transaction->Type = 'supplier_payments';
                $cash_transaction->Details = 'SupplierCashPayment|' . $Id;
                $cash_transaction->Credit = $payments->paidAmount;
                $cash_transaction->Debit = 0.00;
                $cash_transaction->Differentiate = $difference - $payments->paidAmount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $payments->referenceNumber;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->supplierPaymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierCashPayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            } elseif ($payments->payment_type == 'bank') {
                $bankTransaction = BankTransaction::where(['bank_id' => $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference = $Id;
                $bank_transaction->createdDate = $payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type = 'supplier_payments';
                $bank_transaction->Details = 'SupplierBankPayment|' . $Id;
                $bank_transaction->Credit = $payments->paidAmount;
                $bank_transaction->Debit = 0.00;
                $bank_transaction->Differentiate = $difference - $payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->supplierPaymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierBankPayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            } elseif ($payments->payment_type == 'cheque') {
                $bankTransaction = BankTransaction::where(['bank_id' => $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference = $Id;
                $bank_transaction->createdDate = $payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type = 'supplier_payments';
                $bank_transaction->Details = 'SupplierChequePayment|' . $Id;
                $bank_transaction->Credit = $payments->paidAmount;
                $bank_transaction->Debit = 0.00;
                $bank_transaction->Differentiate = $difference - $payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['supplier_id' => $payments->supplier_id,])->get();
                $last_closing = $accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'supplier_id' => $payments->supplier_id,
                        'Debit' => $payments->paidAmount,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing - $payments->paidAmount,
                        'createdDate' => $payments->supplierPaymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'SupplierChequePayment|' . $Id,
                        'referenceNumber' => $payments->referenceNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref = $AccountTransactions->id;
                // new entry done
            }
       //});
        ////////////////// end of account section ////////////////
        return TRUE;
    }

    public function update(Request $request, $Id)
    {
        $user_id = Auth::id();
        $supplier_payment = SupplierPayment::find($Id);
        $supplier_payment->update([
            'paidAmount' => $request->paidAmount,
            'payment_type' => $request->payment_type,
            'bank_id' => $request->bank_id,
            'accountNumber' => $request->accountNumber,
            'TransferDate' => $request->TransferDate,
            'receiptNumber' => $request->receiptNumber,
            'supplierPaymentDate' => $request->paymentReceiveDate,
            'Description' => $request->Description,
            'amountInWords' => Str::getUAECurrency($request->paidAmount),
            'receiverName' => $request->receiverName,
            'user_id' => $user_id,
        ]);
        $Response = SupplierPaymentResource::collection(SupplierPayment::where('id',$Id)->with('supplier_payment_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return array('supplier'=>Supplier::select('id','Name')->where('company_type_id',2)->where('company_id',$company_id)->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'bank'=>Bank::select('id','Name','Description')->orderBy('id','desc')->get());
    }
}
