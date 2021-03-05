<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISalesRepositoryInterface;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\Sales\SalesResource;
use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class SalesRepository implements ISalesRepositoryInterface
{

    public function all()
    {
        return SalesResource::collection(Sale::with('sale_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
//        $sql1="SELECT * FROM sale_details";
//        $row1 = DB::select( DB::raw($sql1));
//        $row1=json_decode(json_encode($row1), true);
//        //echo "<pre>";print_r($row1);die;
//
//        for($i=0;$i<count($row1);$i++)
//        {
//            $total=$row1[$i]['Quantity']*$row1[$i]['Price'];
//            $pad=$row1[$i]['PadNumber'];
//            $sql="UPDATE `sale_details` SET `rowTotal`= ".$total." WHERE `PadNumber`=".$pad;
//            DB::raw($sql);
//            unset($total);
//            unset($pad);
//            unset($sql);
//        }
//        echo "done";die;
//
//        $row=json_decode(json_encode($row), true);
//        $row=array_column($row,'id');
//
//        $sql1="SELECT sale_id FROM sale_details ";
//        $row1 = DB::select( DB::raw($sql1));
//        $row1=json_decode(json_encode($row1), true);
//        $row1=array_column($row1,'sale_id');
//
//        $result=array_diff($row,$row1);
//        echo "<pre>";print_r($result);die;

        return SalesResource::Collection(Sale::with('sale_details','update_notes','documents')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        if ($request->paidBalance == 0.00 || $request->paidBalance == 0) {
            $isPaid = false;
            $partialPaid =false;
        }
        elseif($request->paidBalance >= $request->grandTotal)
        {
            $isPaid = true;
            $partialPaid =false;
        }
        else
        {
            $isPaid = false;
            $partialPaid =true;
        }

        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);

        //$sale_details=$request->sale_details;

        $userId = Auth::id();
        $company_id=Str::getCompany($userId);
        $sales = new Sale();
        $sales->SaleNumber=$newInvoiceID;
        $sales->customer_id=$request->customer_id;
        $sales->SaleDate=$request->SaleDate;
        //$sales->DueDate=$request->DueDate;
        $sales->referenceNumber=$request->referenceNumber;
        $sales->Total=$request->Total;
        $sales->subTotal=$request->subTotal;
        $sales->totalVat=$request->totalVat;
        $sales->grandTotal=$request->grandTotal;
        $sales->paidBalance=$request->paidBalance;
        $sales->remainingBalance=$request->remainingBalance;
        $sales->Description=$request->Description;
        $sales->TermsAndCondition=$request->TermsAndCondition;
        $sales->supplierNote=$request->supplierNote;
        $sales->IsPaid=$isPaid;
        $sales->IsPartialPaid=$partialPaid;
        $sales->createdDate=date('Y-m-d h:i:s');
        $sales->isActive=1;
        $sales->user_id = $userId ?? 0;
        $sales->company_id = $company_id ?? 0;
        $sales->save();
        $sales_id = $sales->id;

        if($request->paidBalance != 0.00 || $request->paidBalance != 0)
        {
            $cash_transaction = new CashTransaction();
            $cash_transaction->Reference=$newInvoiceID;
            $cash_transaction->createdDate=date('Y-m-d h:i:s');
            $cash_transaction->Type='Sales';
            $cash_transaction->Credit=$request->paidBalance;
            $cash_transaction->Debit=0.0;
            $cash_transaction->save();
        }

        $sale_details=json_decode($_POST['sale_details']);

        foreach ($sale_details as $sale_item)
        {
            $data=SaleDetail::create([
                'sale_id'=>$sales_id,
                'PadNumber'=>$sale_item->PadNumber,
                'vehicle_id'=>$sale_item->vehicle_id,
                'product_id'=>$sale_item->product_id,
                'unit_id'=>$sale_item->unit_id,
                'Price'=>$sale_item->Price,
                'Quantity'=>$sale_item->Quantity,
                'rowTotal'=>$sale_item->rowTotal,
                'VAT'=>$sale_item->VAT,
                'rowVatAmount'=>$sale_item->rowVatAmount,
                'rowSubTotal'=>$sale_item->rowSubTotal,
                'Description'=>$sale_item->Description,
                'user_id'=>$userId,
                'company_id'=>$company_id,
            ]);
        }



        ////////////////// account section ////////////////
        if ($sales)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $request->customer_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction))
            {
                if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                    if ($accountTransaction->createdDate != date('Y-m-d')) {
                        $totalCredit = $request->grandTotal;
                    } else {
                        $totalCredit = $accountTransaction->Credit + $request->grandTotal;
                    }
                    $totalDebit = $accountTransaction->Debit;
                    $difference = $accountTransaction->Differentiate + $request->grandTotal;
                }
                elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal )
                {
                    if ($accountTransaction->createdDate != date('Y-m-d')) {
                        $totalDebit = $request->paidBalance;
                        $totalCredit = $request->grandTotal;
                    } else {
                        $totalDebit = $accountTransaction->Debit + $request->paidBalance;
                        $totalCredit = $accountTransaction->Credit + $request->grandTotal;
                    }
                    $differenceValue = $accountTransaction->Differentiate - $request->paidBalance;
                    $difference = $differenceValue + $request->grandTotal;
                }
                else{

                    if ($accountTransaction->createdDate != date('Y-m-d')) {
                        $totalDebit = $request->paidBalance;
                    } else {
                        $totalDebit = $accountTransaction->Debit + $request->paidBalance;
                    }
                    $totalCredit = $accountTransaction->Credit;
                    $difference = $accountTransaction->Differentiate - $request->paidBalance;
                }
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'customer_id'=> $request->customer_id,
                    ])->get();
                if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                    $totalCredit = $request->grandTotal;
                    $totalDebit = $accountTransaction->last()->Debit;
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                }
                elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal )
                {

                    $totalDebit = $request->paidBalance;
                    $totalCredit = $request->grandTotal;
                    $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                    $difference = $differenceValue + $request->grandTotal;
                }
                else{
                    $totalDebit = $request->paidBalance;
                    $totalCredit = $accountTransaction->last()->Credit;
                    $difference = $accountTransaction->last()->Differentiate - $request->paidBalance;
                }
            }
            $AccData =
                [
                    'customer_id' => $request->customer_id,
                    'Credit' => $totalCredit,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $userId,
                ];
            AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'customer_id'   => $request->customer_id,
                ],
                $AccData);
        }
        ////////////////// end of account section ////////////////

        $Response = SalesResource::collection(Sale::where('id',$sales->id)->with(['user','customer','sale_details'])->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function update(SaleRequest $saleRequest, $Id)
    {
        $userId = Auth::id();
        $saleRequest['user_id']=$userId ?? 0;

        //$sale_detail=$saleRequest->sale_details;

        $sales = Sale::findOrFail($Id);

        ////////////////// account section ////////////////
        $accountTransaction = AccountTransaction::where(
            [
                'customer_id'=> $saleRequest->customer_id,
            ])->get();
        if (!is_null($accountTransaction)) {
            $lastAccountTransaction = $accountTransaction->Last();
            if ($lastAccountTransaction->customer_id != $sales->customer_id)
            {
                if ($sales->paidBalance == 0 || $sales->paidBalance == 0.00) {
                    $OldValue1 = $sales->customer->account_transaction->Last()->Credit - $sales->grandTotal;
                    $OldTotalCredit = $OldValue1;
                    $OldTotalDebit = $sales->customer->account_transaction->Last()->Debit;
                    $OldValue = $sales->customer->account_transaction->Last()->Differentiate - $sales->grandTotal;
                    $OldDifference = $OldValue;
                }
                elseif ($sales->paidBalance > 0 AND $sales->paidBalance < $sales->grandTotal)
                {
                    $OldTotalDebit = $sales->customer->account_transaction->Last()->Debit - $sales->paidBalance;
                    $OldTotalCredit = $sales->customer->account_transaction->Last()->Credit - $sales->grandTotal;
                    $differenceValue = $sales->customer->account_transaction->Last()->Differentiate + $sales->paidBalance;
                    $OldDifference = $differenceValue - $sales->grandTotal;
                }
                else{
                    $OldValue1 = $sales->customer->account_transaction->Last()->Debit - $sales->paidBalance;
                    $OldTotalDebit = $OldValue1;
                    $OldTotalCredit = $sales->customer->account_transaction->Last()->Credit;
                    $OldValue = $sales->customer->account_transaction->Last()->Differentiate + $sales->paidBalance;
                    $OldDifference = $OldValue;
                }
                $OldAccData =
                    [
                        'customer_id' => $sales->customer_id,
                        'Debit' => $OldTotalDebit,
                        'Credit' => $OldTotalCredit,
                        'Differentiate' => $OldDifference,
                        'createdDate' => $sales->customer->account_transaction->Last()->createdDate,
                        'user_id' =>$userId,
                    ];
                AccountTransaction::updateOrCreate([
                    'id'   => $sales->customer->account_transaction->Last()->id,
                ], $OldAccData);

                if ($saleRequest->paidBalance == 0 || $saleRequest->paidBalance == 0.00) {
                    $totalCredit = $lastAccountTransaction->Credit + $saleRequest->grandTotal;
                    $totalDebit = $lastAccountTransaction->Debit;
                    $difference = $lastAccountTransaction->Differentiate + $saleRequest->grandTotal;
                }
                elseif ($saleRequest->paidBalance > 0 AND $saleRequest->paidBalance < $saleRequest->grandTotal)
                {
                    $totalDebit = $lastAccountTransaction->Debit - $saleRequest->paidBalance;
                    $totalCredit = $lastAccountTransaction->Credit - $saleRequest->grandTotal;
                    $differenceValue = $accountTransaction->last()->Differentiate + $saleRequest->paidBalance;
                    $difference = $differenceValue - $saleRequest->grandTotal;
                }
                else{
                    $totalDebit = $lastAccountTransaction->Debit + $saleRequest->paidBalance;
                    $totalCredit = $lastAccountTransaction->Credit;
                    $difference = $lastAccountTransaction->Differentiate - $saleRequest->paidBalance;
                }
            }
            else
            {
                if ($saleRequest->paidBalance == 0 || $saleRequest->paidBalance == 0.00 || $saleRequest->paidBalance == "") {

                    if ($lastAccountTransaction->createdDate != $sales->customer->account_transaction->last()->createdDate) {
                        $totalCredit = $saleRequest->grandTotal;
                    } else {
                        $value1 = $lastAccountTransaction->Credit - $sales->grandTotal;
                        $totalCredit = $value1 + $saleRequest->grandTotal;
                    }
                    $totalDebit = $lastAccountTransaction->Debit;
                    $value = $lastAccountTransaction->Differentiate - $sales->grandTotal;
                    $difference = $value + $saleRequest->grandTotal;
//                                        return Response()->json($difference);
                }
                elseif ($saleRequest->paidBalance > 0 AND $saleRequest->paidBalance < $saleRequest->grandTotal)
                {

                    if ($lastAccountTransaction->createdDate != $sales->customer->account_transaction->last()->createdDate) {
                        $totalDebit = $saleRequest->paidBalance;
                        $totalCredit = $saleRequest->grandTotal;
                    } else {
                        $value1 = $lastAccountTransaction->Debit - $sales->paidBalance;
                        $totalDebit = $value1 + $saleRequest->paidBalance;
                        $valueC = $lastAccountTransaction->Debit - $sales->grandTotal;
                        $totalCredit = $valueC + $saleRequest->grandTotal;
                    }
                    $differenceValue = $lastAccountTransaction->Differentiate - $saleRequest->paidBalance;
                    $difference = $differenceValue + $saleRequest->grandTotal;
                }
                else{
                    if ($lastAccountTransaction->createdDate != $sales->customer->account_transaction->last()->createdDate) {
                        $totalDebit = $saleRequest->paidBalance;
                    } else {
                        $value1 = $lastAccountTransaction->Debit - $sales->paidBalance;
                        $totalDebit = $value1 + $saleRequest->paidBalance;
                    }
                    $totalCredit = $lastAccountTransaction->Credit;
                    $value = $lastAccountTransaction->Differentiate + $sales->paidBalance;
                    $difference = $value - $saleRequest->paidBalance;
                }
            }

            $AccData =
                [
                    'customer_id' => $saleRequest->customer_id,
                    'Credit' => $totalCredit,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => $lastAccountTransaction->createdDate,
                    'user_id' =>$userId,
                ];
            AccountTransaction::updateOrCreate([
                'createdDate'   => $lastAccountTransaction->createdDate,
                'id'   => $lastAccountTransaction->id,
            ], $AccData);
        }
        ////////////////// end of account section ////////////////

        if ($saleRequest->paidBalance == 0.00 || $saleRequest->paidBalance == 0) {
            $isPaid = false;
            $partialPaid =false;
        }
        elseif($saleRequest->paidBalance >= $saleRequest->grandTotal)
        {
            $isPaid = true;
            $partialPaid =false;
        }
        else
        {
            $isPaid = false;
            $partialPaid =true;
        }

        $sales->customer_id=$saleRequest->customer_id;
        $sales->SaleDate=$saleRequest->SaleDate;
        //$sales->DueDate=$saleRequest->DueDate;
        $sales->referenceNumber=$saleRequest->referenceNumber;
        $sales->Total=$saleRequest->Total;
        $sales->subTotal=$saleRequest->subTotal;
        $sales->totalVat=$saleRequest->totalVat;
        $sales->grandTotal=$saleRequest->grandTotal;
        $sales->paidBalance=$saleRequest->paidBalance;
        $sales->remainingBalance=$saleRequest->remainingBalance;
        $sales->Description=$saleRequest->Description;
        $sales->TermsAndCondition=$saleRequest->TermsAndCondition;
        $sales->supplierNote=$saleRequest->supplierNote;
        $sales->IsPaid=$isPaid;
        $sales->IsPartialPaid=$partialPaid;
        $sales->update();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'sales';
        $update_note->RelationId = $Id;
        $update_note->Description = $saleRequest->update_note;
        $update_note->user_id = $userId;
        $update_note->save();

        //DB::table('sale_details')->where([['sale_id', $Id]])->delete();
        SaleDetail::where('sale_id', array($Id))->delete();

        $sale_detail=json_decode($_POST['sale_details']);
        if(!empty($sale_detail))
        {
            foreach ($sale_detail as $sale_item)
            {
                $data=SaleDetail::create([
                    'sale_id'=>$Id,
                    'PadNumber'=>$sale_item->PadNumber,
                    'vehicle_id'=>$sale_item->vehicle_id,
                    'product_id'=>$sale_item->product_id,
                    'Price'=>$sale_item->Price,
                    'Quantity'=>$sale_item->Quantity,
                    'rowTotal'=>$sale_item->rowTotal,
                    'VAT'=>$sale_item->VAT,
                    'rowVatAmount'=>$sale_item->rowVatAmount,
                    'rowSubTotal'=>$sale_item->rowSubTotal,
                    'Description'=>$sale_item->Description,
                ]);
            }
        }
        $Response = SalesResource::collection(Sale::where('id',$Id)->with('user','customer','sale_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = SalesResource::collection(Sale::where('id',$Id)->with('user','customer','sale_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        $userId = Auth::id();
        return array('pad_number'=>$this->PadNumber(),'products'=>Product::select('id','Name')->with(['api_units'=>function($q){$q->select('id','Name','product_id');}])->orderBy('id','desc')->get(),'customer'=>Customer::select('id','Name')->with(['customer_prices'=>function($q){$q->select('id','customer_id','Rate','VAT','customerLimit');},'vehicles'=>function($q){$q->select('id','registrationNumber','customer_id');}])->where('company_id',Str::getCompany($userId))->orderBy('id','desc')->get());
    }

    public function PadNumber()
    {
//        $PadNumber = new SaleDetail();
//        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);
//        return $newPad;

        $data=array();
        $max_sales_id = SaleDetail::where('company_id',session('company_id'))->find(DB::table('sale_details')->max('id'));
        //echo "<pre>";print_r($max_sales_id);die;
        if($max_sales_id)
        {
            $lastPad = $max_sales_id->PadNumber;
            $lastDate = $max_sales_id->createdDate;
            if(!is_numeric($lastPad))
            {
                $data['pad_no']=1;
                $data['last_date']=date('Y-m-d');
            }
            else
            {
                $data['pad_no']=$lastPad + 1;
                $data['last_date']=$lastDate;
            }
        }
        else
        {
            $data['pad_no']=1;
            $data['last_date']=date('Y-m-d');
        }
        return $data;
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Sale::find($Id);
        $update->user_id=$userId;
        $update->save();
        $sales = Sale::withoutTrashed()->find($Id);
        if($sales->trashed())
        {
            return new SalesResource(Sale::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('sale_details')->where([['sale_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            $sales->delete();
            return new SalesResource(Sale::onlyTrashed()->find($Id));
        }
    }

    public function SalesDocumentsUpload(Request $request)
    {
        try
        {
            $userId = Auth::id();
            if ($request->hasfile('document'))
            {
                foreach($request->file('document') as $document)
                {
                    $extension = $document->getClientOriginalExtension();
                    $filename=uniqid('sales_doc_'.$request->id.'_').'.'.$extension;
                    $document->storeAs('document/',$filename,'public');

                    $file_upload = new FileUpload();
                    $file_upload->Title = $filename;
                    $file_upload->RelationTable = 'sales';
                    $file_upload->RelationId = $request->id;
                    $file_upload->user_id = $userId;
                    $file_upload->save();
                }
            }
            else
            {
                return $this->userResponse->Failed("user Image","file not found");
            }
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function print($Id)
    {
        $data=$this->getById($Id);
        //echo "<pre>";print_r($data);die;
        if(!empty($data['sale_details']))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('times', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetXY(25,7);
            $pdf::SetFont('times', '', 12);
            $pdf::MultiCell(83, 5, $company_title, 0, 'R', 0, 2, '', '', true, 0);
            $pdf::SetFont('times', '', 8);

            $pdf::SetXY(25,12);
            $pdf::MultiCell(134, 5, $company_address, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(44, 5, $data['SaleNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['SaleDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['DueDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['sale_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>SALES INVOICE</b></u>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $created_by=isset($data['user']['name'])?$data['user']['name']:'N.A.';
            $vendor=isset($data['supplier']['Name'])?$data['supplier']['Name']:'N.A.';
            $email=isset($data['vendor']['Name'])?$data['vendor']['Name']:'N.A.';
            $phone=isset($data['vendor']['Mobile'])?$data['vendor']['Mobile']:'N.A.';
            $address=isset($data['vendor']['Address'])?$data['vendor']['Address']:'N.A.';
            $pdf::SetFont('times', '', 10);
            $pdf::Cell(95, 5, 'SUPPLIER :','B',0,'L');
            $pdf::Cell(95, 5, 'Created By : '.$created_by,'',0,'R');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Name : '.$vendor,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Email : '.$email,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Phone : '.$phone,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Address : '.$address,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, '','',0,'');
            $pdf::Ln(6);

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0.5" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="30">S/N</th>
                    <th align="center" width="190">Product</th>
                    <th align="center" width="70">PadNO</th>
                    <th align="center" width="50">Unit</th>
                    <th align="center" width="55">Price</th>
                    <th align="center" width="50">Quantity</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="80">Subtotal</th>
                </tr>';
            $pdf::SetFont('times', '', 10);
            $subtotal=0.0;
            $vat_total=0.0;
            $grand_total=0.0;
            $sn=0;
            for($i=0;$i<count($row);$i++)
            {
//                if($row[$i]['deleted_at']=='1970-01-01T08:00:00.000000Z')
//                {

                $html .='<tr>
                    <td align="center" width="30">'.($sn+1).'</td>
                    <td align="left" width="190">'.$row[$i]['api_product']['Name'].'</td>
                    <td align="left" width="70">'.$row[$i]['PadNumber'].'</td>
                    <td align="center" width="50">'.'N.A.'.'</td>
                    <td align="center" width="55">'.number_format($row[$i]['Price'],2,'.',',').'</td>
                    <td align="center" width="50">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    <td align="center" width="35">'.number_format($row[$i]['VAT'],2,'.',',').'</td>
                    <td align="right" width="80">'.number_format($row[$i]['rowSubTotal'],2,'.',',').'</td>
                    </tr>';
                $sn++;
                //}
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table><table border="0" cellpadding="5">';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" style="border: 1px solid black;">Terms & Conditions :</td>
                    <td width="175" colspan="4" style="border: 1px solid black;">Vendor Note :</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($subtotal,2,'.',',').'</td>
                </tr>';
            $terms_condition=isset($data['TermsAndCondition'])?$data['TermsAndCondition']:'N.A.';
            $vendor_note=isset($data['supplierNote'])?$data['supplierNote']:'N.A.';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" rowspan="2" style="border: 1px solid black;">'.$data['TermsAndCondition'].'</td>
                    <td width="175" colspan="4" rowspan="2" style="border: 1px solid black;">'.$data['supplierNote'].'</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">VAT (5%)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['totalVat'],2,'.',',').'</td>
                </tr>';
            $html.= '
                <tr color="black">
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Grand Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['grandTotal'],2,'.',',').'</td>
                </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, true, false, '');

            $amount_in_words=Str::getUAECurrency($data['grandTotal']);
            $pdf::Cell(95, 5, 'Amount in Words : '.$amount_in_words,'',0,'L');
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);

            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $pdf::Cell(95, 5, 'Accepted By (Name & Signature) :','',0,'C');
            $pdf::Cell(95, 5, 'Issued By (Name & Signature): ','',0,'C');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/sales_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/sales_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
        }
    }

    public function ActivateDeactivate($Id)
    {
        $sales = Sale::find($Id);
        if($sales->isActive==1)
        {
            $sales->isActive=0;
        }
        else
        {
            $sales->isActive=1;
        }
        $sales->update();
        return new SalesResource(Sale::find($Id));
    }

    public function customerSaleDetails($Id)
    {
        $sales = Sale::with('customer.vehicles','sale_details')
            ->where([
                'customer_id'=>$Id,
                'IsPaid'=> false,
            ])->get();
        return $sales;
    }
}
