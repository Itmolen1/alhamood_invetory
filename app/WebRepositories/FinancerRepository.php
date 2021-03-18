<?php


namespace App\WebRepositories;


use App\Http\Requests\FinancerRequest;
use App\Models\AccountTransaction;
use App\Models\Financer;
use App\WebRepositories\Interfaces\IFinancerRepositoryInterface;
use Illuminate\Http\Request;

class FinancerRepository implements IFinancerRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Financer::where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('financer.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    return $button;
                })
                ->rawColumns([
                    'action',
                ])
                ->make(true);
        }
        return view('admin.financer.index');
    }

    public function create()
    {
        return view('admin.financer.create');
    }

    public function store(FinancerRequest $financerRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $financer = [
            'Name' =>$financerRequest->Name,
            'Mobile' =>$financerRequest->Mobile,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'Description' =>$financerRequest->Description,
            'openingBalance' =>$financerRequest->openingBalance,
            'openingBalanceAsOfDate' =>$financerRequest->openingBalanceAsOfDate,
        ];
        $financer = Financer::create($financer);
        if ($financer)
        {
            //account entry
            $account = [
                'financer_id' => $financer->id,
                'user_id' => $user_id,
                'createdDate' => $financerRequest->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Description' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$financerRequest->openingBalance,
            ];
            $accountTransaction = AccountTransaction::create($account);
        }
        return redirect()->route('financer.index');
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
        return redirect()->route('financer.index');
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($id)
    {
        $financer = Financer::find($id);
        return view('admin.financer.edit',compact('financer'));
    }
}
