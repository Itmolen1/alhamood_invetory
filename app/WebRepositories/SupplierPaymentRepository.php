<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\PaymentReceive;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use Illuminate\Http\Request;

class SupplierPaymentRepository implements ISupplierPaymentRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        if(request()->ajax())
        {
            return datatables()->of(SupplierPayment::with('user','company','supplier')->latest()->get())
                ->addColumn('action', function ($data) {

                    $button = '<a href="'.route('supplier_payments.show', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-bars"></i></a>';
                    $button .='&nbsp;';
                    return $button;
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Quantity";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('supplier_payment_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
//                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                        $button .='&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        return $button;
                    }
                })
                ->rawColumns(
                    [
                        'action',
                        'push',
                        'supplier',
                    ])
                ->make(true);
        }
        return view('admin.supplier_payment.index');
    }

    public function create()
    {
        // TODO: Implement create() method.
        $suppliers = Supplier::all();
        $banks = Bank::all();
        return view('admin.supplier_payment.create',compact('suppliers','banks'));
    }

    public function store(Request $request)
    {
        // TODO: Implement store() method.

        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0) {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $payment = new SupplierPayment();
            $payment->supplier_id = $request->Data['supplier_id'];
            $payment->totalAmount = $request->Data['totalAmount'];
            $payment->payment_type = $request->Data['payment_type'];
            $payment->referenceNumber = $request->Data['referenceNumber'];
            $payment->supplierPaymentDate = $request->Data['supplierPaymentDate'];
            $payment->paidAmount = $request->Data['paidAmount'];
            $payment->amountInWords = $request->Data['amountInWords'];
            $payment->receiptNumber = $request->Data['receiptNumber'];
            $payment->receiverName = $request->Data['receiverName'];
            $payment->transferDate = $request->Data['TransferDate'];
            $payment->accountNumber = $request->Data['accountNumber'];
            $payment->Description = $request->Data['Description'];
            $payment->bank_id = $request->Data['bank_id'] ?? 0;
            $payment->user_id = $user_id;
            $payment->createdDate = date('Y-m-d');
            $payment->company_id = $company_id;
            $payment->save();
            $payment = $payment->id;
            $amount = 0;
            foreach($request->Data['orders'] as $detail)
            {
                $amount += $detail['amountPaid'];

                if ($amount <= $request->Data['paidAmount'])
                {
                    $isPaid = true;
                    $isPartialPaid = false;
                    $totalAmount = $detail['amountPaid'];
                }
                elseif($amount >= $request->Data['paidAmount']){
//                    if ($detail['amountPaid'] > $request->Data['paidAmount']) {
                        $isPaid = false;
                        $isPartialPaid = true;
                        $totalAmount1 = $amount - $request->Data['paidAmount'];
                        $totalAmount = $detail['amountPaid'] - $totalAmount1;
//                    }
                }

                $data =  SupplierPaymentDetail::create([
                    "amountPaid"        => $totalAmount,
                    "purchase_id"        => $detail['purchase_id'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "supplier_payment_id"      => $payment,
                    'createdDate' => date('Y-m-d')
                ]);


                $sale = Purchase::find($detail['purchase_id']);
                $sale->update([
                    "paidBalance"        => $totalAmount + $sale->paidBalance,
                    "remainingBalance"   => $sale->remainingBalance - $totalAmount,
                    "IsPaid" => $isPaid,
                    "IsPartialPaid" => $isPartialPaid,
                    "IsNeedStampOrSignature" => false,
                ]);
            }
            return Response()->json($amount);
        }
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
        $supplier_payment_details = SupplierPaymentDetail::with('user','company','supplier_payment.supplier')->where('supplier_payment_id',$Id)->get();
//        dd($payment_receives);
        return view('admin.supplier_payment.show',compact('supplier_payment_details'));
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function supplier_payments_push(Request $request, $Id)
    {
        // TODO: Implement supplier_payments_push() method.
        $payments = SupplierPayment::with('supplier')->find($Id);
        //dd($advance->Amount);

        $user_id = session('user_id');
        $payments->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);
        ////////////////// account section ////////////////
        if ($payments)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'supplier_id'=> $payments->supplier_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalCredit = $payments->paidAmount;
                }
                else
                {
                    $totalCredit = $accountTransaction->Credit + $payments->paidAmount;
                }
                $difference = $accountTransaction->Differentiate + $payments->paidAmount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'supplier_id'=> $payments->supplier_id,
                    ])->get();
                $totalCredit = $payments->paidAmount;
                $difference = $accountTransaction->last()->Differentiate + $payments->paidAmount;
            }
            $AccData =
                [
                    'supplier_id' => $payments->supplier_id,
                    'Credit' => $totalCredit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            $AccountTransactions = AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'supplier_id'   => $payments->supplier_id,
                ],
                $AccData);
            //return Response()->json($AccountTransactions);
            // return Response()->json("");
        }
        ////////////////// end of account section ////////////////
        return redirect()->route('supplier_payments.index')->with('pushed','Your Account Debit Successfully');
    }
}
