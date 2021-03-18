<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Financer;
use App\Models\LoanMaster;
use App\Models\LoanPaymentMaster;
use App\WebRepositories\Interfaces\IInwardLoanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InwardLoanRepository implements IInwardLoanRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(LoanMaster::with('financer')->where('loanType',1)->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    if($data->isPushed == false) {
                        $button = '<a href="' . route('inward_loans.edit', $data->id) . '"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        return $button;
                    }
                    elseif($data->isPushed==1 && $data->inward_isPaid==0){
                        $button = '<a href="' . url('inward_loan_payment', $data->id) . '"  class=" btn btn-primary btn-sm">Pay</a>';
                        return $button;
                    }
                    elseif($data->isPushed==1 && $data->inward_isPaid==1){
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Paid</i></button>';
                        return $button;
                    }
                    else{
                        return 'N.A.';
                    }
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('inward_loan_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->addColumn('financer', function($data) {
                    return $data->financer->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'push',
                ])
                ->make(true);
        }
        return view('admin.inward_loan.index');
    }

    public function create()
    {
        $financers=Financer::get();
        $banks = Bank::all();
        return view('admin.inward_loan.create',compact('financers','banks'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $advance = [
                'financer_id' =>$request->financer_id,
                'referenceNumber' =>$request->referenceNumber,
                'loanType' =>1,
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
        return redirect()->route('inward_loans.index');
    }

    public function update(Request $request, $id)
    {
        $financer = Financer::find($id);

        $user_id = session('user_id');
        $financer->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'user_id' =>$user_id,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('inward_loans.index');
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function inward_loan_push($Id)
    {
        DB::transaction(function () use($Id)
        {
            $loan = LoanMaster::with('financer')->find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            if($loan->totalAmount>0)
            {
                $accountTransaction = AccountTransaction::where(['financer_id'=> $loan->financer_id,])->get();
                $closing_before=$accountTransaction->last()->Differentiate;

                $accountTransaction_ref=0;
                // account section by gautam //
                if($loan->payment_type == 'cash')
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$Id;
                    $cash_transaction->createdDate=$loan->loanDate;
                    $cash_transaction->Type='inward_loan';
                    $cash_transaction->Details='IncomingCashLoan|'.$Id;
                    $cash_transaction->Credit=0.00;
                    $cash_transaction->Debit=$loan->totalAmount;
                    $cash_transaction->Differentiate=$difference+$loan->totalAmount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['financer_id'=> $loan->financer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'financer_id' => $loan->financer_id,
                            'Debit' => 0.00,
                            'Credit' => $loan->totalAmount,
                            'Differentiate' => $last_closing+$loan->totalAmount,
                            'createdDate' => $loan->loanDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'IncomingCashLoan|'.$Id,
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
                    $bank_transaction->Type='inward_loan';
                    $bank_transaction->Details='IncomingBankLoan|'.$Id;
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$loan->totalAmount;
                    $bank_transaction->Differentiate=$difference+$loan->totalAmount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $loan->bank_id;
                    $bank_transaction->updateDescription = $loan->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['financer_id'=> $loan->financer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'financer_id' => $loan->financer_id,
                            'Debit' => 0.00,
                            'Credit' => $loan->totalAmount,
                            'Differentiate' => $last_closing+$loan->totalAmount,
                            'createdDate' => $loan->transferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'IncomingBankLoan|'.$Id,
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
                    $bank_transaction->Type='customer_advances';
                    $bank_transaction->Details='CustomerChequeAdvance|'.$Id;
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$loan->totalAmount;
                    $bank_transaction->Differentiate=$difference+$loan->totalAmount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $loan->bank_id;
                    $bank_transaction->updateDescription = $loan->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['financer_id'=> $loan->financer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'financer_id' => $loan->financer_id,
                            'Debit' => 0.00,
                            'Credit' => $loan->totalAmount,
                            'Differentiate' => $last_closing-$loan->totalAmount,
                            'createdDate' => $loan->transferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'SupplierChequeAdvance|'.$Id,
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
                    'inward_PaidBalance' =>0.00,
                    'inward_RemainingBalance' =>$loan->totalAmount,
                    'user_id' =>$user_id,
                ]);
            }
        });
        return redirect()->route('inward_loans.index')->with('pushed','Your Account Debit Successfully');
    }

    public function inward_loan_payment($id)
    {
        $inward_loan = LoanMaster::with('financer')->find($id);
        $banks = Bank::all();
        return view('admin.inward_loan.get_payment',compact('inward_loan','banks'));
    }

    public function inward_loan_save_payment(Request $request, $id)
    {
        DB::transaction(function () use($request,$id)
        {
            $inward_loan = LoanMaster::with('financer')->find($id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            $payment = [
                'amountPaid' =>$request->amountPaid,
                'referenceNumber' =>$request->referenceNumber,
                'loanType' =>1,
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
            if($inward_loan->inward_RemainingBalance-$request->amountPaid==0)
            {
                $is_paid=1;
                $is_partial_paid=0;
            }
            else
            {
                $is_partial_paid=1;
            }
            $inward_loan->update([
                'inward_PaidBalance'=>$inward_loan->inward_PaidBalance+$request->amountPaid,
                'inward_RemainingBalance'=>$inward_loan->inward_RemainingBalance-$request->amountPaid,
                'Description'=>$request->referenceNumber,
                'inward_isPaid'=>$is_paid,
                'inward_isPartialPaid'=>$is_partial_paid,
            ]);

            if($request->paymentType == 'cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$id;
                $cash_transaction->createdDate=$request->paymentDate ?? date('Y-m-d h:i:s');
                $cash_transaction->Type='loan_payment_masters';
                $cash_transaction->Details='InwardLoanCashPayment|'.$id;
                $cash_transaction->Credit=$request->amountPaid;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$request->amountPaid;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $request->referenceNumber;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['financer_id'=> $inward_loan->financer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'financer_id' => $inward_loan->financer_id,
                        'Debit' => $request->amountPaid,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing-$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'InwardLoanCashPayment|'.$id,
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
                $bank_transaction->Details='InwardLoanBankPayment|'.$id;
                $bank_transaction->Credit=$request->amountPaid;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$request->amountPaid;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = $request->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['financer_id'=> $inward_loan->financer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'financer_id' => $inward_loan->financer_id,
                        'Debit' => $request->amountPaid,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing-$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'InwardLoanBankPayment|'.$id,
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
                $bank_transaction->Details='InwardLoanChequePayment|'.$id;
                $bank_transaction->Credit=$request->amountPaid;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$request->amountPaid;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = $request->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['financer_id'=> $inward_loan->financer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'financer_id' => $inward_loan->financer_id,
                        'Debit' => $request->amountPaid,
                        'Credit' => 0.00,
                        'Differentiate' => $last_closing-$request->amountPaid,
                        'createdDate' => $request->paymentDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'InwardLoanChequePayment|'.$id,
                        'referenceNumber'=>$request->referenceNumber,
                        'updateDescription'=>$request->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                // new entry done
            }
        });
        return redirect()->route('inward_loans.index');
    }

    public function edit($id)
    {
        $financer = Financer::find($id);
        return view('admin.financer.edit',compact('financer'));
    }
}
