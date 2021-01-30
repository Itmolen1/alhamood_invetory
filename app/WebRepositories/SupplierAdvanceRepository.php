<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierAdvanceRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\CustomerAdvance;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class SupplierAdvanceRepository implements ISupplierAdvanceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(SupplierAdvance::with('supplier')->latest()->get())
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Data";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('supplier_advances_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<a href="'.route('supplier_advances.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .='&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->rawColumns(
                    [
                        'push',
                        'supplier',
                    ])
                ->make(true);
        }
        return view('admin.supplierAdvance.index');
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $banks = Bank::all();
        return view('admin.supplierAdvance.create',compact('suppliers','banks'));
    }

    public function store(SupplierAdvanceRequest $supplierAdvanceRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $advance = [
            'receiptNumber' =>$supplierAdvanceRequest->receiptNumber,
            'paymentType' =>$supplierAdvanceRequest->paymentType,
            'Amount' =>$supplierAdvanceRequest->amount,
            'sumOf' =>$supplierAdvanceRequest->amountInWords,
            'receiverName' =>$supplierAdvanceRequest->receiverName,
            'accountNumber' =>$supplierAdvanceRequest->accountNumber,
            'ChequeNumber' =>$supplierAdvanceRequest->ChequeNumber,
            'TransferDate' =>$supplierAdvanceRequest->TransferDate,
            'registerDate' =>$supplierAdvanceRequest->registerDate,
            'bank_id' =>$supplierAdvanceRequest->bank_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'supplier_id' =>$supplierAdvanceRequest->supplier_id ?? 0,
            'Description' =>$supplierAdvanceRequest->Description,
        ];
        SupplierAdvance::create($advance);
        return redirect()->route('supplier_advances.index');
    }

    public function update(Request $request, $Id)
    {
        $advance = SupplierAdvance::find($Id);

        $user_id = session('user_id');
        $advance->update([
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->amount,
            'sumOf' =>$request->amountInWords,
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
        return redirect()->route('supplier_advances.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $suppliers = Supplier::all();
        $banks = Bank::all();
        $supplierAdvance = SupplierAdvance::with('supplier')->find($Id);
        return view('admin.supplierAdvance.edit',compact('suppliers','supplierAdvance','banks'));
    }

    public function delete(Request $request, $Id)
    {
        $data = SupplierAdvance::findOrFail($Id);
        $data->delete();
        return redirect()->route('supplier_advances.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }


    public function supplier_advances_push(Request $request, $Id)
    {
        $advance = SupplierAdvance::with('supplier')->find($Id);

        $user_id = session('user_id');
        $company_id = session('company_id');
        $advance->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        /* account section by gautam */
        if($advance->paymentType == 'cash')
        {
            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
            $difference = $cashTransaction->last()->Differentiate;
            $cash_transaction = new CashTransaction();
            $cash_transaction->Reference=$Id;
            $cash_transaction->createdDate=$advance->TransferDate;
            $cash_transaction->Type='supplier_advances';
            $cash_transaction->Details='SupplierCashAdvance|'.$Id;
            $cash_transaction->Credit=$advance->Amount;
            $cash_transaction->Debit=0.00;
            $cash_transaction->Differentiate=$difference-$advance->Amount;
            $cash_transaction->user_id = $user_id;
            $cash_transaction->company_id = $company_id;
            $cash_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $advance->supplier_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'supplier_id' => $advance->supplier_id,
                    'Debit' => $advance->Amount,
                    'Credit' => 0.00,
                    'Differentiate' => $last_closing-$advance->Amount,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'SupplierCashAdvance|'.$Id,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            // new entry done
        }
        elseif ($advance->paymentType == 'bank')
        {
            $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
            $difference = $bankTransaction->last()->Differentiate;
            $bank_transaction = new BankTransaction();
            $bank_transaction->Reference=$Id;
            $bank_transaction->createdDate=$advance->TransferDate;
            $bank_transaction->Type='supplier_payments';
            $bank_transaction->Details='SupplierBankAdvance|'.$Id;
            $bank_transaction->Credit=$advance->Amount;
            $bank_transaction->Debit=0.00;
            $bank_transaction->Differentiate=$difference-$advance->Amount;
            $bank_transaction->user_id = $user_id;
            $bank_transaction->company_id = $company_id;
            $bank_transaction->bank_id = $advance->bank_id;
            $bank_transaction->updateDescription = $advance->ChequeNumber;
            $bank_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $advance->supplier_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'supplier_id' => $advance->supplier_id,
                    'Debit' => $advance->Amount,
                    'Credit' => 0.00,
                    'Differentiate' => $last_closing-$advance->Amount,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'SupplierBankAdvance|'.$Id,
                    'referenceNumber'=>$advance->ChequeNumber,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            // new entry done
        }
        elseif ($advance->paymentType == 'cheque')
        {
            $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
            $difference = $bankTransaction->last()->Differentiate;
            $bank_transaction = new BankTransaction();
            $bank_transaction->Reference=$Id;
            $bank_transaction->createdDate=$advance->TransferDate;
            $bank_transaction->Type='supplier_payments';
            $bank_transaction->Details='SupplierChequeAdvance|'.$Id;
            $bank_transaction->Credit=$advance->Amount;
            $bank_transaction->Debit=0.00;
            $bank_transaction->Differentiate=$difference-$advance->Amount;
            $bank_transaction->user_id = $user_id;
            $bank_transaction->company_id = $company_id;
            $bank_transaction->bank_id = $advance->bank_id;
            $bank_transaction->updateDescription = $advance->ChequeNumber;
            $bank_transaction->save();

            // start new entry
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $advance->supplier_id,])->get();
            $last_closing=$accountTransaction->last()->Differentiate;
            $AccData =
                [
                    'supplier_id' => $advance->supplier_id,
                    'Debit' => $advance->Amount,
                    'Credit' => 0.00,
                    'Differentiate' => $last_closing-$advance->Amount,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'SupplierChequeAdvance|'.$Id,
                    'referenceNumber'=>$advance->ChequeNumber,
                ];
            $AccountTransactions = AccountTransaction::Create($AccData);
            // new entry done
        }
        /* account section by gautam */



//        ////////////////// account section ////////////////
//        if ($advance)
//        {
//            $accountTransaction = AccountTransaction::where(
//                [
//                    'supplier_id'=> $advance->supplier_id,
//                    'createdDate' => date('Y-m-d'),
//                ])->first();
//            if (!is_null($accountTransaction)) {
//                if ($accountTransaction->createdDate != date('Y-m-d')) {
//                    $totalCredit = $advance->Amount;
//                }
//                else
//                {
//                    $totalCredit = $accountTransaction->Credit + $advance->Amount;
//                }
//                $difference = $accountTransaction->Differentiate + $advance->Amount;
//            }
//            else
//            {
//                $accountTransaction = AccountTransaction::where(
//                    [
//                        'supplier_id'=> $advance->supplier_id,
//                    ])->get();
//                $totalCredit = $advance->Amount;
//                $difference = $accountTransaction->last()->Differentiate + $advance->Amount;
//            }
//            $AccData =
//                [
//                    'supplier_id' => $advance->supplier_id,
//                    'Credit' => $totalCredit,
//                    'Differentiate' => $difference,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                ];
//            $AccountTransactions = AccountTransaction::updateOrCreate(
//                [
//                    'createdDate'   => date('Y-m-d'),
//                    'supplier_id'   => $advance->supplier_id,
//                ],
//                $AccData);
//            //return Response()->json($AccountTransactions);
//            // return Response()->json("");
//        }
//        ////////////////// end of account section ////////////////
        return redirect()->route('supplier_advances.index')->with('pushed','Your Account Debit Successfully');
    }
}
