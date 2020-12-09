<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceRepository implements ICustomerAdvanceRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $customerAdvances = CustomerAdvance::with('user','customer')->get();
        return view('admin.customerAdvance.index',compact('customerAdvances'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $customers = Customer::all();
        $banks = Bank::all();
        return view('admin.customerAdvance.create',compact('customers','banks'));
    }

    public function store(CustomerAdvanceRequest $customerAdvanceRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $advance = [
            'receiptNumber' =>$customerAdvanceRequest->receiptNumber,
            'paymentType' =>$customerAdvanceRequest->paymentType,
            'Amount' =>$customerAdvanceRequest->amount,
            'sumOf' =>$customerAdvanceRequest->amountInWords,
            'receiverName' =>$customerAdvanceRequest->receiverName,
            'accountNumber' =>$customerAdvanceRequest->accountNumber ?? 0,
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
        // TODO: Implement update() method.
        $advance = CustomerAdvance::find($Id);

        $user_id = session('user_id');
        $advance->update([
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->amount,
            'sumOf' =>$request->amountInWords,
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber ?? null,
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
        // TODO: Implement edit() method.
        $customers = Customer::all();
        $banks = Bank::all();
        $customerAdvance = CustomerAdvance::with('customer')->find($Id);
        return view('admin.customerAdvance.edit',compact('customers','customerAdvance','banks'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
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
        // TODO: Implement customer_advances_push() method.
        $advance = CustomerAdvance::with('customer')->find($Id);
        //dd($advance->Amount);

        $user_id = session('user_id');
        $advance->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);
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
                $totalDebit = $advance->customer_id;
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
        return redirect()->route('customer_advances.index')->with('pushed','Your Account Debit Successfully');
    }
}
