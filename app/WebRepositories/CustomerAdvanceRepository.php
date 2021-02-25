<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Sale;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceRepository implements ICustomerAdvanceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(CustomerAdvance::with('user','customer')->latest()->get())
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Data";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('customer_advances_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<a href="'.route('customer_advances.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .='&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->addColumn('disburse', function($data) {
                    if($data->IsSpent == 0){
                        $button = '<form action="'. url('customer_advances_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<a href="'.route('customer_advances.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .='&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-battery-full"> Disburse</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-battery-empty"> Disbursed</i></button>';
                        return $button;
                    }
                })
                ->rawColumns(
                    [
                        'push',
                        'customer',
                    ])
                ->make(true);
        }
        return view('admin.customerAdvance.index');
    }

    public function create()
    {
        $customers = Customer::all();
        $banks = Bank::all();
        return view('admin.customerAdvance.create',compact('customers','banks'));
    }

    public function store(CustomerAdvanceRequest $customerAdvanceRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $advance = [
            'receiptNumber' =>$customerAdvanceRequest->receiptNumber,
            'paymentType' =>$customerAdvanceRequest->paymentType,
            'Amount' =>$customerAdvanceRequest->amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$customerAdvanceRequest->amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>$customerAdvanceRequest->amountInWords,
            'receiverName' =>$customerAdvanceRequest->receiverName,
            'accountNumber' =>$customerAdvanceRequest->accountNumber ?? 0,
            'ChequeNumber' =>$customerAdvanceRequest->ChequeNumber,
            'TransferDate' =>$customerAdvanceRequest->TransferDate ?? 0,
            'registerDate' =>$customerAdvanceRequest->registerDate,
            'bank_id' =>$customerAdvanceRequest->bank_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'customer_id' =>$customerAdvanceRequest->customer_id ?? 0,
            'Description' =>$customerAdvanceRequest->Description,
        ];
        CustomerAdvance::create($advance);
        return redirect()->route('customer_advances.index');
    }

    public function update(Request $request, $Id)
    {
        $advance = CustomerAdvance::find($Id);

        $user_id = session('user_id');
        $advance->update([
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$request->amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>$request->amountInWords,
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber ?? null,
            'ChequeNumber' =>$request->ChequeNumber ?? 0,
            'TransferDate' =>$request->TransferDate,
            'registerDate' =>$request->registerDate,
            'bank_id' =>$request->bank_id ?? 0,
            'user_id' =>$user_id,
            'customer_id' =>$request->customer_id ?? null,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('customer_advances.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $customers = Customer::all();
        $banks = Bank::all();
        $customerAdvance = CustomerAdvance::with('customer')->find($Id);
        return view('admin.customerAdvance.edit',compact('customers','customerAdvance','banks'));
    }

    public function delete(Request $request, $Id)
    {
        $data = CustomerAdvance::findOrFail($Id);
        $data->delete();
        return redirect()->route('customer_advances.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function customer_advances_push(Request $request, $Id)
    {
        $advance = CustomerAdvance::with('customer')->find($Id);

        $user_id = session('user_id');
        $company_id = session('company_id');
        $advance->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        if($advance->Amount>0)
        {
            $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
            $closing_before_advance_credit=$accountTransaction->last()->Differentiate;

            $accountTransaction_ref=0;
            // account section by gautam //
            if($advance->paymentType == 'cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$Id;
                $cash_transaction->createdDate=$advance->TransferDate;
                $cash_transaction->Type='customer_advances';
                $cash_transaction->Details='CustomerCashAdvance|'.$Id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$advance->Amount;
                $cash_transaction->Differentiate=$difference+$advance->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $advance->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $advance->Amount,
                        'Differentiate' => $last_closing-$advance->Amount,
                        'createdDate' => $advance->TransferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CustomerCashAdvance|'.$Id,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
            elseif ($advance->paymentType == 'bank')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$Id;
                $bank_transaction->createdDate=$advance->TransferDate;
                $bank_transaction->Type='customer_advances';
                $bank_transaction->Details='CustomerBankAdvance|'.$Id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$advance->Amount;
                $bank_transaction->Differentiate=$difference+$advance->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $advance->bank_id;
                $bank_transaction->updateDescription = $advance->ChequeNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $advance->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $advance->Amount,
                        'Differentiate' => $last_closing-$advance->Amount,
                        'createdDate' => $advance->TransferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CustomerBankAdvance|'.$Id,
                        'referenceNumber'=>$advance->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
            elseif ($advance->paymentType == 'cheque')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$Id;
                $bank_transaction->createdDate=$advance->TransferDate;
                $bank_transaction->Type='customer_advances';
                $bank_transaction->Details='CustomerChequeAdvance|'.$Id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$advance->Amount;
                $bank_transaction->Differentiate=$difference+$advance->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $advance->bank_id;
                $bank_transaction->updateDescription = $advance->ChequeNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $advance->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $advance->Amount,
                        'Differentiate' => $last_closing-$advance->Amount,
                        'createdDate' => $advance->TransferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'SupplierChequeAdvance|'.$Id,
                        'referenceNumber'=>$advance->ChequeNumber,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
            // account section by gautam //

            //now since account is affected we need to auto pay same amount to purchase entries only if last closing is positive value

            /*if($closing_before_advance_credit>0)
            {
                //we have entries without payment made so make it paid until advance amount becomes zero
                // bring all unpaid purchase records
                $all_sales = Sale::with('customer','sale_details')->where([
                    'customer_id'=>$Id,
                    'IsPaid'=> false,
                ])->orderBy('SaleDate')->get();
                //dd($all_purchase);
                $total_i_have=$advance->Amount;

                foreach($all_sales as $sale)
                {
                    $total_you_need = $sale->remainingBalance;
                    $still_payable_to_you=0;
                    $total_giving_to_you=0;
                    $isPartialPaid = 0;
                    if ($total_i_have >= $total_you_need)
                    {
                        $isPaid = 1;
                        $isPartialPaid = 0;
                        $total_i_have = $total_i_have - $total_you_need;

                        $this_sale = Sale::find($sale->id);
                        $this_sale->update([
                            "paidBalance"        => $sale->grandTotal,
                            "remainingBalance"   => $still_payable_to_you,
                            "IsPaid" => $isPaid,
                            "IsPartialPaid" => $isPartialPaid,
                            "IsNeedStampOrSignature" => false,
                            "Description" => 'AutoPaid|'.$advance->id,
                            "account_transaction_payment_id" => $accountTransaction_ref,
                        ]);
                    }
                    else
                    {
                        $isPaid = 0;
                        $isPartialPaid = 1;
                        $total_giving_to_you=$total_i_have;
                        $total_i_have = $total_i_have - $total_giving_to_you;

                        $this_sale = Sale::find($sale->id);
                        $this_sale->update([
                            "paidBalance"        => $sale->paidBalance+$total_giving_to_you,
                            "remainingBalance"   => $sale->remainingBalance-$total_giving_to_you,
                            "IsPaid" => $isPaid,
                            "IsPartialPaid" => $isPartialPaid,
                            "IsNeedStampOrSignature" => false,
                            "Description" => 'AutoPaid|'.$advance->id,
                            "account_transaction_payment_id" => $accountTransaction_ref,
                        ]);
                    }

                    if($total_i_have<=0)
                    {
                        break;
                    }
                }
            }*/
        }

        /* account section by gautam */
//        if($advance->paymentType == 'cash')
//        {
//            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
//            $difference = $cashTransaction->last()->Differentiate;
//            $cash_transaction = new CashTransaction();
//            $cash_transaction->Reference=$Id;
//            $cash_transaction->createdDate=$advance->TransferDate;
//            $cash_transaction->Type='customer_advances';
//            $cash_transaction->Details='CustomerCashAdvance|'.$Id;
//            $cash_transaction->Credit=0.00;
//            $cash_transaction->Debit=$advance->Amount;
//            $cash_transaction->Differentiate=$difference+$advance->Amount;
//            $cash_transaction->user_id = $user_id;
//            $cash_transaction->company_id = $company_id;
//            $cash_transaction->save();
//
//            // start new entry
//            $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
//            $last_closing=$accountTransaction->last()->Differentiate;
//            $AccData =
//                [
//                    'customer_id' => $advance->customer_id,
//                    'Debit' => 0.00,
//                    'Credit' => $advance->Amount,
//                    'Differentiate' => $last_closing-$advance->Amount,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                    'company_id' => $company_id,
//                    'Description'=>'CustomerCashAdvance|'.$Id,
//                ];
//            $AccountTransactions = AccountTransaction::Create($AccData);
//            // new entry done
//        }
//        elseif ($advance->paymentType == 'bank')
//        {
//            $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
//            $difference = $bankTransaction->last()->Differentiate;
//            $bank_transaction = new BankTransaction();
//            $bank_transaction->Reference=$Id;
//            $bank_transaction->createdDate=$advance->TransferDate;
//            $bank_transaction->Type='customer_advances';
//            $bank_transaction->Details='CustomerBankAdvance|'.$Id;
//            $bank_transaction->Credit=0.00;
//            $bank_transaction->Debit=$advance->Amount;
//            $bank_transaction->Differentiate=$difference+$advance->Amount;
//            $bank_transaction->user_id = $user_id;
//            $bank_transaction->company_id = $company_id;
//            $bank_transaction->bank_id = $advance->bank_id;
//            $bank_transaction->updateDescription = $advance->ChequeNumber;
//            $bank_transaction->save();
//
//            // start new entry
//            $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
//            $last_closing=$accountTransaction->last()->Differentiate;
//            $AccData =
//                [
//                    'customer_id' => $advance->customer_id,
//                    'Debit' => 0.00,
//                    'Credit' => $advance->Amount,
//                    'Differentiate' => $last_closing-$advance->Amount,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                    'company_id' => $company_id,
//                    'Description'=>'CustomerBankAdvance|'.$Id,
//                    'referenceNumber'=>$advance->ChequeNumber,
//                ];
//            $AccountTransactions = AccountTransaction::Create($AccData);
//            // new entry done
//        }
//        elseif ($advance->paymentType == 'cheque')
//        {
//            $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
//            $difference = $bankTransaction->last()->Differentiate;
//            $bank_transaction = new BankTransaction();
//            $bank_transaction->Reference=$Id;
//            $bank_transaction->createdDate=$advance->TransferDate;
//            $bank_transaction->Type='customer_advances';
//            $bank_transaction->Details='CustomerChequeAdvance|'.$Id;
//            $bank_transaction->Credit=0.00;
//            $bank_transaction->Debit=$advance->Amount;
//            $bank_transaction->Differentiate=$difference+$advance->Amount;
//            $bank_transaction->user_id = $user_id;
//            $bank_transaction->company_id = $company_id;
//            $bank_transaction->bank_id = $advance->bank_id;
//            $bank_transaction->updateDescription = $advance->ChequeNumber;
//            $bank_transaction->save();
//
//            // start new entry
//            $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
//            $last_closing=$accountTransaction->last()->Differentiate;
//            $AccData =
//                [
//                    'customer_id' => $advance->customer_id,
//                    'Debit' => 0.00,
//                    'Credit' => $advance->Amount,
//                    'Differentiate' => $last_closing-$advance->Amount,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                    'company_id' => $company_id,
//                    'Description'=>'CustomerChequeAdvance|'.$Id,
//                    'referenceNumber'=>$advance->ChequeNumber,
//                ];
//            $AccountTransactions = AccountTransaction::Create($AccData);
//            // new entry done
//        }
        /* account section by gautam */

        /*
        ////////////////// account section ////////////////
        if ($advance)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $advance->customer_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalDebit = $advance->Amount;
                }
                else
                {
                    $totalDebit = $accountTransaction->Debit + $advance->Amount;
                }
                $difference = $accountTransaction->Differentiate - $advance->Amount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'customer_id'=> $advance->customer_id,
                    ])->get();
                $totalDebit = $advance->Amount;
                $difference = $accountTransaction->last()->Differentiate - $advance->Amount;
            }
            $AccData =
                [
                    'customer_id' => $advance->customer_id,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            $AccountTransactions = AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'customer_id'   => $advance->customer_id,
                ],
                $AccData);
            //return Response()->json($AccountTransactions);
            // return Response()->json("");
        }
        ////////////////// end of account section ////////////////
        /// */

        $advance->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        return redirect()->route('customer_advances.index')->with('pushed','Your Account Debit Successfully');
    }
}
