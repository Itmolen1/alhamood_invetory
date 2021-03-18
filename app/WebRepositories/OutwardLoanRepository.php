<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\LoanMaster;
use App\Models\LoanPaymentMaster;
use App\WebRepositories\Interfaces\IOutwardLoandRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutwardLoanRepository implements IOutwardLoandRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(LoanMaster::with('customer')->where('loanType',0)->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    if($data->isPushed == false) {
                        $button = '<a href="' . route('outward_loans.edit', $data->id) . '"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        return $button;
                    }
                    elseif($data->isPushed==1 && $data->outward_isPaid==0){
                        $button = '<a href="' . url('outward_loan_payment', $data->id) . '"  class=" btn btn-primary btn-sm">Receive</a>';
                        return $button;
                    }
                    elseif($data->isPushed==1 && $data->outward_isPaid==1){
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban">Received</i></button>';
                        return $button;
                    }
                    else{
                        return 'N.A.';
                    }
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('outward_loan_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'push',
                ])
                ->make(true);
        }
        return view('admin.outward_loan.index');
    }

    public function create()
    {
        $customers=Customer::where('company_id',session('company_id'))->orderBy('id', 'desc')->get();
        $banks = Bank::all();
        return view('admin.outward_loan.create',compact('customers','banks'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $advance = [
                'customer_id' =>$request->customer_id,
                'referenceNumber' =>$request->referenceNumber,
                'loanType' =>0,
                'loanDate' =>$request->loanDate,
                'Description' =>$request->Description,
                'totalAmount' =>$request->totalAmount,
                'amountInWords' =>$request->amountInWords,
                'payment_type' =>$request->paymentType,
                'TransferDate' =>$request->TransferDate,
                'bank_id' =>$request->bank_id ?? 0,
                'accountNumber' =>$request->accountNumber ?? 0,
                'ChequeNumber' =>$request->ChequeNumber ?? 0,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            LoanMaster::create($advance);
        });
        return redirect()->route('outward_loans.index');
    }

    public function update(Request $request, $id)
    {
        $customer = Financer::find($id);

        $user_id = session('user_id');
        $customer->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'user_id' =>$user_id,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('outward_loans.index');
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function outward_loan_push($Id)
    {
        DB::transaction(function () use($Id)
        {
            $loan = LoanMaster::with('customer')->find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            if($loan->totalAmount>0)
            {
                $accountTransaction_ref=0;
                // account section by gautam //
                if($loan->payment_type == 'cash')
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$Id;
                    $cash_transaction->createdDate=$loan->loanDate;
                    $cash_transaction->Type='outward_loan';
                    $cash_transaction->Details='OutgoingCashLoan|'.$Id;
                    $cash_transaction->Credit=$loan->totalAmount;
                    $cash_transaction->Debit=0.00;
                    $cash_transaction->Differentiate=$difference-$loan->totalAmount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $loan->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $loan->customer_id,
                            'Debit' => $loan->totalAmount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing-$loan->totalAmount,
                            'createdDate' => $loan->loanDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'OutgoingCashLoan|'.$Id,
                            'referenceNumber'=>$loan->referenceNumber,
                            'updateDescription'=>$loan->ChequeNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                elseif ($loan->payment_type == 'bank')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $loan->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$Id;
                    $bank_transaction->createdDate=$loan->transferDate;
                    $bank_transaction->Type='outward_loan';
                    $bank_transaction->Details='OutgoingBankLoan|'.$Id;
                    $bank_transaction->Credit=$loan->totalAmount;
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$loan->totalAmount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $loan->bank_id;
                    $bank_transaction->updateDescription = $loan->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $loan->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $loan->customer_id,
                            'Debit' => $loan->totalAmount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing-$loan->totalAmount,
                            'createdDate' => $loan->transferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'OutgoingBankLoan|'.$Id,
                            'referenceNumber'=>$loan->referenceNumber,
                            'updateDescription'=>$loan->ChequeNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                elseif ($loan->payment_type == 'cheque')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $loan->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$Id;
                    $bank_transaction->createdDate=$loan->transferDate;
                    $bank_transaction->Type='outward_loan';
                    $bank_transaction->Details='OutgoingChequeLoan|'.$Id;
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$loan->totalAmount;
                    $bank_transaction->Differentiate=$difference+$loan->totalAmount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $loan->bank_id;
                    $bank_transaction->updateDescription = $loan->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $loan->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $loan->customer_id,
                            'Debit' => 0.00,
                            'Credit' => $loan->totalAmount,
                            'Differentiate' => $last_closing-$loan->totalAmount,
                            'createdDate' => $loan->transferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'OutgoingChequeLoan|'.$Id,
                            'referenceNumber'=>$loan->referenceNumber,
                            'updateDescription'=>$loan->ChequeNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                // account section by gautam //

                $loan->update([
                    'isPushed' =>1,
                    'outward_PaidBalance' =>0.00,
                    'outward_RemainingBalance' =>$loan->totalAmount,
                    'user_id' =>$user_id,
                ]);
            }
        });
        return redirect()->route('outward_loans.index')->with('pushed','Your Account Debit Successfully');
    }

    public function outward_loan_payment($id)
    {
        $outward_loan = LoanMaster::with('customer')->find($id);
        $banks = Bank::all();
        return view('admin.outward_loan.get_payment',compact('outward_loan','banks'));
    }

    public function outward_loan_save_payment(Request $request, $id)
    {
        DB::transaction(function () use($request,$id)
        {
            $outward_loan = LoanMaster::with('customer')->find($id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            $payment = [
                'amountPaid' =>$request->amountPaid,
                'referenceNumber' =>$request->referenceNumber,
                'loanType' =>0,
                'paymentDate' =>$request->paymentDate,
                'loan_master_id' =>$id,
                'Description' =>$request->Description,
                'payment_type' =>$request->paymentType,
                'bank_id' =>$request->bank_id ?? 0,
                'accountNumber' =>$request->accountNumber ?? 0,
                'ChequeNumber' =>$request->ChequeNumber ?? 0,
                'transferDate' =>$request->TransferDate,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            LoanPaymentMaster::create($payment);

            $is_paid=0;
            if($outward_loan->outward_RemainingBalance-$request->amountPaid==0)
            {
                $is_paid=1;
                $is_partial_paid=0;
            }
            else
            {
                $is_partial_paid=1;
            }
            $outward_loan->update([
                'outward_PaidBalance'=>$outward_loan->outward_PaidBalance+$request->amountPaid,
                'outward_RemainingBalance'=>$outward_loan->outward_RemainingBalance-$request->amountPaid,
                'Description'=>$request->referenceNumber,
                'outward_isPaid'=>$is_paid,
                'outward_isPartialPaid'=>$is_partial_paid,
            ]);

            if($request->paymentType == 'cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$id;
                $cash_transaction->createdDate=$request->paymentDate ?? date('Y-m-d h:i:s');
                $cash_transaction->Type='loan_payment_masters';
                $cash_transaction->Details='outwardLoanCashPayment|'.$id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$request->amountPaid;
                $cash_transaction->Differentiate=$difference+$request->amountPaid;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $request->referenceNumber;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $outward_loan->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $outward_loan->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $request->amountPaid,
                        'Differentiate' => $last_closing+$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'outwardLoanCashPayment|'.$id,
                        'referenceNumber'=>$request->referenceNumber,
                        'updateDescription'=>$request->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                // new entry done
            }
            elseif ($request->paymentType == 'bank')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$id;
                $bank_transaction->createdDate=$request->paymentDate ?? date('Y-m-d');
                $bank_transaction->Type='loan_payment_masters';
                $bank_transaction->Details='outwardLoanBankPayment|'.$id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$request->amountPaid;
                $bank_transaction->Differentiate=$difference+$request->amountPaid;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = $request->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $outward_loan->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $outward_loan->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $request->amountPaid,
                        'Differentiate' => $last_closing+$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'outwardLoanBankPayment|'.$id,
                        'referenceNumber'=>$request->referenceNumber,
                        'updateDescription'=>$request->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                // new entry done
            }
            elseif ($request->paymentType == 'cheque')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$id;
                $bank_transaction->createdDate=$request->paymentDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='loan_payment_masters';
                $bank_transaction->Details='outwardLoanChequePayment|'.$id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$request->amountPaid;
                $bank_transaction->Differentiate=$difference+$request->amountPaid;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = $request->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $outward_loan->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $outward_loan->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $request->amountPaid,
                        'Differentiate' => $last_closing+$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'outwardLoanChequePayment|'.$id,
                        'referenceNumber'=>$request->referenceNumber,
                        'updateDescription'=>$request->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                // new entry done
            }
        });
        return redirect()->route('outward_loans.index');
    }

    public function edit($id)
    {
        $customer = Financer::find($id);
        return view('admin.customer.edit',compact('customer'));
    }
}
