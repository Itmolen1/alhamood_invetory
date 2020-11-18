<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierAdvanceRequest;
use App\Models\Bank;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class SupplierAdvanceRepository implements ISupplierAdvanceRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $supplierAdvances = SupplierAdvance::with('supplier')->get();
        //dd($supplierAdvances);
        return view('admin.supplierAdvance.index',compact('supplierAdvances'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $suppliers = Supplier::all();
        $banks = Bank::all();
        return view('admin.supplierAdvance.create',compact('suppliers','banks'));
    }

    public function store(SupplierAdvanceRequest $supplierAdvanceRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $advance = [
            'receiptNumber' =>$supplierAdvanceRequest->receiptNumber,
            'paymentType' =>$supplierAdvanceRequest->paymentType,
            'Amount' =>$supplierAdvanceRequest->amount,
            'sumOf' =>$supplierAdvanceRequest->amountInWords,
            'receiverName' =>$supplierAdvanceRequest->receiverName,
            'accountNumber' =>$supplierAdvanceRequest->accountNumber ? $supplierAdvanceRequest->accountNumber:null,
            'TransferDate' =>$supplierAdvanceRequest->TransferDate ? $supplierAdvanceRequest->TransferDate:null,
            'registerDate' =>$supplierAdvanceRequest->registerDate,
            'bank_id' =>$supplierAdvanceRequest->bank_id,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'supplier_id' =>$supplierAdvanceRequest->supplier_id,
            'Description' =>$supplierAdvanceRequest->Description,
        ];
        SupplierAdvance::create($advance);
        return redirect()->route('supplier_advances.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $advance = SupplierAdvance::find($Id);

        $user_id = session('user_id');
        $advance->update([
            'receiptNumber' =>$request->receiptNumber,
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->amount,
            'sumOf' =>$request->amountInWords,
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber ? $request->accountNumber:null,
            'TransferDate' =>$request->TransferDate,
            'registerDate' =>$request->registerDate,
            'bank_id' =>$request->bank_id,
            'user_id' =>$user_id,
            'supplier_id' =>$request->supplier_id,
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
        // TODO: Implement edit() method.
        $suppliers = Supplier::all();
        $banks = Bank::all();
        $supplierAdvance = SupplierAdvance::with('supplier')->find($Id);
        return view('admin.supplierAdvance.edit',compact('suppliers','supplierAdvance','banks'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
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
}
