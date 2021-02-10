<?php


namespace App\WebRepositories;


use App\Http\Requests\DepositRequest;
use App\Models\Bank;
use App\Models\Deposit;
use App\WebRepositories\Interfaces\IDepositRepositoryInterface;
use Illuminate\Http\Request;

class DepositRepository implements IDepositRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Deposit::with('bank')->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('deposits.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('deposits.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('bankName', function($data) {
                    return $data->bank->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'Amount',
                    'Reference',
                    'depositDate',
                ])
                ->make(true);
        }
        return view('admin.deposit.index');
    }

    public function create()
    {
        $banks = Bank::all();
        return view('admin.deposit.create',compact('banks'));
    }

    public function store(DepositRequest $depositRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $deposit = [
            'Amount' =>$depositRequest->Amount,
            'bank_id' =>$depositRequest->bank_id,
            'Reference' =>strip_tags($depositRequest->Reference),
            'depositDate' =>$depositRequest->depositDate,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        $deposit = Deposit::create($deposit);

        // start accounting //
//        if ($deposit)
//        {
//            $account = new AccountTransaction([
//                'customer_id' => $customer->id,
//                'user_id' => $user_id,
//                'createdDate' => date('Y-m-d'),
//                'company_id' =>$company_id,
//                'Description' =>'initial',
//                'Credit' =>0.00,
//                'Debit' =>0.00,
//                'Differentiate' =>$customerRequest->openingBalance,
//            ]);
//        }
//        $customer->account_transaction()->save($account);
        // end accounting //

        return redirect()->route('deposits.index');
    }

    public function update(Request $request, $Id)
    {
        $deposit = Deposit::find($Id);

        $user_id = session('user_id');
        $deposit->update([
            'Amount' =>$request->Amount,
            'bank_id' =>$request->bank_id,
            'Reference' =>strip_tags($request->Reference),
            'depositDate' =>$request->depositDate,
            'user_id' =>$user_id,
        ]);

        // start accounting //
        // end accounting //
        return redirect()->route('deposits.index');
    }

    public function edit($Id)
    {
        $banks = Bank::all();
        $deposit = Deposit::with('bank')->find($Id);
        return view('admin.deposit.edit',compact('deposit','banks'));
    }
}
