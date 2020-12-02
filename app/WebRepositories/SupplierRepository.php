<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierRequest;
use App\Models\CompanyType;
use App\Models\PaymentType;
use App\Models\PaymentTerm;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\AccountTransaction;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use Illuminate\Http\Request;

class SupplierRepository implements ISupplierRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        // $suppliers = Supplier::with('company','user')->get();
        // return view('admin.supplier.index',compact('suppliers'));
        if(request()->ajax())
        {
            return datatables()->of(Supplier::with('company','user','payment_type','company_type','payment_term')->latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('suppliers.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('suppliers.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('suppliers.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('suppliers.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                ->addColumn('paymentType', function($data) {
                    return $data->payment_type->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'paymentType'
                ])
                ->make(true);
        }
        return view('admin.supplier.index');
    }

    public function create()
    {
        // TODO: Implement create() method.
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        return view('admin.supplier.create',compact('regions','payment_types','company_types','payment_terms'));
    }

    public function store(SupplierRequest $supplierRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($supplierRequest->hasFile('fileUpload'))
            $filename = $supplierRequest->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = null;

        $supplier = [
            'Name' =>$supplierRequest->Name,
            'Mobile' =>$supplierRequest->Mobile,
            'Representative' =>$supplierRequest->Representative,
            'Phone' =>$supplierRequest->Phone,
            'Address' =>$supplierRequest->Address,
            'postCode' =>$supplierRequest->postCode,
            'region_id' =>$supplierRequest->region_id ?? 0,
            'Email' =>$supplierRequest->Email,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$supplierRequest->Description,
            'registrationDate' =>$supplierRequest->registrationDate,
            'TRNNumber' =>$supplierRequest->TRNNumber,
            'payment_term_id' =>$supplierRequest->paymentTerm ?? 0,
            'company_type_id' =>$supplierRequest->companyType ?? 0,
            'payment_type_id' =>$supplierRequest->paymentType ?? 0,
        ];
        $supplier = Supplier::create($supplier);
        if ($supplier) {
            $account = new AccountTransaction([
                'supplier_id' => $supplier->id,
                'user_id' => $user_id,
            ]);
        }
        $supplier->account_transaction()->save($account);
        return redirect()->route('suppliers.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $supplier = Supplier::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = $supplier->fileUpload;

        $user_id = session('user_id');
        $supplier->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id ?? 0,
            'user_id' =>$user_id,
            'Email' =>$request->Email,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'payment_term_id' =>$request->paymentTerm ?? 0,
            'company_type_id' =>$request->companyType ?? 0,
            'payment_type_id' =>$request->paymentType ?? 0,

        ]);
        return redirect()->route('suppliers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        $supplier = Supplier::with('region','payment_type','company_type','payment_term')->find($Id);
        return view('admin.supplier.edit',compact('supplier','regions','payment_types','company_types','payment_terms'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Supplier::findOrFail($Id);
        $data->delete();
        return redirect()->route('suppliers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function supplierDetails($Id)
    {
        // TODO: Implement supplierDetails() method.
        $suppliers = Supplier::find($Id);
        return response()->json($suppliers);
    }
}
