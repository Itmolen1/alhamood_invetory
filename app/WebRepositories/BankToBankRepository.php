<?php


namespace App\WebRepositories;


use App\Models\Bank;
use App\Models\BankToBank;
use App\Models\BankTransaction;
use App\WebRepositories\Interfaces\IBankToBankRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankToBankRepository implements IBankToBankRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(BankToBank::with('from_bank','to_bank')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('bank_to_banks.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    //$button .= '<a href="'.route('bank_to_banks.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    //$button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('FromBank', function($data) {
                    return $data->from_bank->Name ?? "No Data";
                })
                ->addColumn('ToBank', function($data) {
                    return $data->to_bank->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'Amount',
                    'Reference',
                    'depositDate',
                ])
                ->make(true);
        }
        return view('admin.bank_to_bank.index');
    }

    public function create()
    {
        $banks = Bank::all();
        return view('admin.bank_to_bank.create',compact('banks'));
    }

    public function store(Request $request)
    {
        if($request->from_bank_id==$request->to_bank_id)
        {
            return redirect()->route('bank_to_banks.create');
        }
        DB::transaction(function () use($request) {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $bank_to_bank = [
                'Amount' =>$request->Amount,
                'from_bank_id' =>$request->from_bank_id,
                'to_bank_id' =>$request->to_bank_id,
                'Reference' =>strip_tags($request->Reference),
                'depositDate' =>$request->depositDate,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            $bank_to_bank = BankToBank::create($bank_to_bank);
            $bank_to_bank = $bank_to_bank->id;

            // start accounting //
            if ($bank_to_bank)
            {
                // credit from bank account
                $bankTransaction = BankTransaction::where(['bank_id'=> $request->from_bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bankTransaction = new BankTransaction();
                $bankTransaction->Reference=$bank_to_bank;
                $bankTransaction->createdDate=$request->depositDate;
                $bankTransaction->Type='bank_to_bank';
                $bankTransaction->Details='BankToBank|'.$bank_to_bank;
                $bankTransaction->Credit=$request->Amount;
                $bankTransaction->Debit=0.00;
                $bankTransaction->Differentiate=$difference-$request->Amount;
                $bankTransaction->user_id = $user_id;
                $bankTransaction->company_id = $company_id;
                $bankTransaction->bank_id = $request->from_bank_id;
                $bankTransaction->updateDescription = strip_tags($request->Reference);
                $bankTransaction->save();

                // debit to bank account
                $bankTransaction = BankTransaction::where(['bank_id'=> $request->to_bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$bank_to_bank;
                $bank_transaction->createdDate=$request->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='bank_to_bank';
                $bank_transaction->Details='BankToBank|'.$bank_to_bank;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$request->Amount;
                $bank_transaction->Differentiate=$difference+$request->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->to_bank_id;
                $bank_transaction->updateDescription = strip_tags($request->Reference);
                $bank_transaction->save();
            }
            // end accounting //
        });
        return redirect()->route('bank_to_banks.index');
    }

    public function update(Request $request, $Id)
    {
//        DB::transaction(function () use($request,$Id) {
//            $deposited = Deposit::find($Id);
//
//            $user_id = session('user_id');
//            $company_id = session('company_id');
//
//            // start reverse accounting //
//            if($deposited)
//            {
//                // credit bank account and debit cash account
//                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
//                $difference = $cashTransaction->last()->Differentiate;
//                $cash_transaction = new CashTransaction();
//                $cash_transaction->Reference=$deposited->id;
//                $cash_transaction->createdDate=$deposited->depositDate;
//                $cash_transaction->Type='deposits';
//                $cash_transaction->Details='DepositReverse|'.$deposited->id;
//                $cash_transaction->Credit=0.00;
//                $cash_transaction->Debit=$deposited->Amount;
//                $cash_transaction->Differentiate=$difference+$deposited->Amount;
//                $cash_transaction->user_id = $user_id;
//                $cash_transaction->company_id = $company_id;
//                $cash_transaction->PadNumber = $deposited->Reference;
//                $cash_transaction->save();
//
//                $bankTransaction = BankTransaction::where(['bank_id'=> $deposited->bank_id])->get();
//                $difference = $bankTransaction->last()->Differentiate;
//                $bank_transaction = new BankTransaction();
//                $bank_transaction->Reference=$deposited->id;
//                $bank_transaction->createdDate=$deposited->depositDate ?? date('Y-m-d h:i:s');
//                $bank_transaction->Type='deposits';
//                $bank_transaction->Details='DepositReverse|'.$deposited->id;
//                $bank_transaction->Credit=$deposited->Amount;
//                $bank_transaction->Debit=0.00;
//                $bank_transaction->Differentiate=$difference-$deposited->Amount;
//                $bank_transaction->user_id = $user_id;
//                $bank_transaction->company_id = $company_id;
//                $bank_transaction->bank_id = $deposited->bank_id;
//                $bank_transaction->updateDescription = strip_tags($deposited->Reference);
//                $bank_transaction->save();
//            }
//            // end reverse accounting //
//
//            // start accounting //
//            if($deposited)
//            {
//                // credit cash account and debit bank account
//                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
//                $difference = $cashTransaction->last()->Differentiate;
//                $cash_transaction = new CashTransaction();
//                $cash_transaction->Reference=$deposited->id;
//                $cash_transaction->createdDate=$request->depositDate;
//                $cash_transaction->Type='deposits';
//                $cash_transaction->Details='Deposit|'.$deposited->id;
//                $cash_transaction->Credit=$request->Amount;
//                $cash_transaction->Debit=0.00;
//                $cash_transaction->Differentiate=$difference-$request->Amount;
//                $cash_transaction->user_id = $user_id;
//                $cash_transaction->company_id = $company_id;
//                $cash_transaction->PadNumber = strip_tags($request->Reference);
//                $cash_transaction->save();
//
//                $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
//                $difference = $bankTransaction->last()->Differentiate;
//                $bank_transaction = new BankTransaction();
//                $bank_transaction->Reference=$deposited->id;
//                $bank_transaction->createdDate=$request->depositDate ?? date('Y-m-d h:i:s');
//                $bank_transaction->Type='deposits';
//                $bank_transaction->Details='Deposit|'.$deposited->id;
//                $bank_transaction->Credit=0.00;
//                $bank_transaction->Debit=$request->Amount;
//                $bank_transaction->Differentiate=$difference+$request->Amount;
//                $bank_transaction->user_id = $user_id;
//                $bank_transaction->company_id = $company_id;
//                $bank_transaction->bank_id = $request->bank_id;
//                $bank_transaction->updateDescription = strip_tags($request->Reference);
//                $bank_transaction->save();
//            }
//            // end accounting //
//
//            $deposited->update([
//                'Amount' =>$request->Amount,
//                'bank_id' =>$request->bank_id,
//                'Reference' =>strip_tags($request->Reference),
//                'depositDate' =>$request->depositDate,
//                'user_id' =>$user_id,
//            ]);
//        });
        return redirect()->route('bank_to_banks.index');
    }

    public function edit($Id)
    {
        $banks = Bank::all();
        return view('admin.bank_to_bank.edit',compact('banks'));
    }
}
