<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\update_note;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class PurchaseRepository implements IPurchaseRepositoryInterface
{

    public function all()
    {
        return PurchaseResource::collection(Purchase::with('purchase_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return PurchaseResource::Collection(Purchase::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $invoice = new Purchase();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'PUR-00'.($lastInvoiceID + 1);

        $purchase_detail=$request->purchase_detail;

        $userId = Auth::id();
        $purchase = new Purchase();
        $purchase->PurchaseNumber=$newInvoiceID;
        $purchase->supplier_id=$request->supplier_id;
        $purchase->employee_id=$request->employee_id;
        $purchase->PurchaseDate=$request->PurchaseDate;
        $purchase->DueDate=$request->DueDate;
        $purchase->referenceNumber=$request->referenceNumber;
        $purchase->Total=$request->Total;
        $purchase->subTotal=$request->subTotal;
        $purchase->totalVat=$request->totalVat;
        $purchase->grandTotal=$request->grandTotal;
        $purchase->Description=$request->Description;
        $purchase->TermsAndCondition=$request->TermsAndCondition;
        $purchase->supplierNote=$request->supplierNote;
        $purchase->IsNeedStampOrSignature=$request->IsNeedStampOrSignature;
        $purchase->createdDate=date('Y-m-d h:i:s');
        $purchase->isActive=1;
        $purchase->user_id = $userId ?? 0;
        $purchase->save();
        $purchase_id = $purchase->id;

        foreach ($purchase_detail as $purchase_item)
        {
            $data=PurchaseDetail::create([
                'purchase_id'=>$purchase_id,
                'PadNumber'=>$purchase_item['PadNumber'],
                'product_id'=>$purchase_item['product_id'],
                'Price'=>$purchase_item['Price'],
                'Quantity'=>$purchase_item['Quantity'],
                'rowTotal'=>$purchase_item['rowTotal'],
                'VAT'=>$purchase_item['VAT'],
                'rowVatAmount'=>$purchase_item['rowVatAmount'],
                'rowSubTotal'=>$purchase_item['rowSubTotal'],
                'Description'=>$purchase_item['Description'],
            ]);
        }
        $Response = PurchaseResource::collection(Purchase::where('id',$purchase->id)->with('user','supplier','purchase_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
        //return new PurchaseResource(Purchase::find($purchase->id));
    }

    public function update(PurchaseRequest $purchaseRequest, $Id)
    {
        $userId = Auth::id();
        $purchaseRequest['user_id']=$userId ?? 0;

        $purchase_detail=$purchaseRequest->purchase_detail;

        $purchase = Purchase::findOrFail($Id);
        $purchase->supplier_id=$purchaseRequest->supplier_id;
        $purchase->employee_id=$purchaseRequest->employee_id;
        $purchase->PurchaseDate=$purchaseRequest->PurchaseDate;
        $purchase->DueDate=$purchaseRequest->DueDate;
        $purchase->referenceNumber=$purchaseRequest->referenceNumber;
        $purchase->Total=$purchaseRequest->Total;
        $purchase->subTotal=$purchaseRequest->subTotal;
        $purchase->totalVat=$purchaseRequest->totalVat;
        $purchase->grandTotal=$purchaseRequest->grandTotal;
        $purchase->Description=$purchaseRequest->Description;
        $purchase->TermsAndCondition=$purchaseRequest->TermsAndCondition;
        $purchase->supplierNote=$purchaseRequest->supplierNote;
        $purchase->IsNeedStampOrSignature=$purchaseRequest->IsNeedStampOrSignature;
        $purchase->update();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'purchases';
        $update_note->RelationId = $Id;
        $update_note->Description = $purchaseRequest->update_note;
        $update_note->user_id = $userId;
        $update_note->save();

        DB::table('purchase_details')->where([['purchase_id', $Id]])->delete();

        if(!empty($purchase_detail))
        {
            foreach ($purchase_detail as $purchase_item)
            {
                $data=PurchaseDetail::create([
                    'purchase_id'=>$Id,
                    'PadNumber'=>$purchase_item['PadNumber'],
                    'product_id'=>$purchase_item['product_id'],
                    'Price'=>$purchase_item['Price'],
                    'Quantity'=>$purchase_item['Quantity'],
                    'rowTotal'=>$purchase_item['rowTotal'],
                    'VAT'=>$purchase_item['VAT'],
                    'rowVatAmount'=>$purchase_item['rowVatAmount'],
                    'rowSubTotal'=>$purchase_item['rowSubTotal'],
                    'Description'=>$purchase_item['Description'],
                ]);
            }
        }
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        return array('products'=>Product::select('id','Name')->orderBy('id','desc')->get(),'supplier'=>Supplier::select('id','Name')->orderBy('id','desc')->get());
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Purchase::find($Id);
        $update->user_id=$userId;
        $update->save();
        $purchase = Purchase::withoutTrashed()->find($Id);
        if($purchase->trashed())
        {
            return new PurchaseResource(Purchase::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('purchase_details')->where([['purchase_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            $purchase->delete();
            return new PurchaseResource(Purchase::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier = Purchase::onlyTrashed()->find($Id);
        if (!is_null($supplier))
        {
            $supplier->restore();
            return new PurchaseResource(Purchase::find($Id));
        }
        return new PurchaseResource(Purchase::find($Id));
    }

    public function trashed()
    {
        $supplier = Purchase::onlyTrashed()->get();
        return PurchaseResource::collection($supplier);
    }

    public function PurchaseDocumentsUpload(Request $request)
    {
        try
        {
            $userId = Auth::id();
            if ($request->hasfile('document'))
            {
                foreach($request->file('document') as $document)
                {
                    $extension = $document->getClientOriginalExtension();
                    $filename=uniqid('purchase_doc_'.$request->id.'_').'.'.$extension;
                    $document->storeAs('document/',$filename,'public');

                    $file_upload = new FileUpload();
                    $file_upload->Title = $filename;
                    $file_upload->RelationTable = 'purchases';
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

    public function print($id)
    {
        $data=$this->getById($id);
        //echo "<pre>";print_r($data);die;
        if(!empty($data['purchase_details']))
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
            $pdf::MultiCell(44, 5, $data['PurchaseNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['PurchaseDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['DueDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['purchase_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>PURCHASE INVOICE</b></u>';
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
                    <td align="left" width="190">'.$row[$i]['product']['Name'].'</td>
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

            $amount_in_words=$this->getUAECurrency($data['grandTotal']);
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
            $fileLocation = storage_path().'/app/public/purchase_order_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/purchase_order_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
        }
    }

    function getUAECurrency(float $number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'one', 2 => 'two',
            3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
            7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred','thousand','lakh', 'crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Fils' : '';
        return ($Rupees ? $Rupees . 'AED ' : '') . $paise;
    }
}
