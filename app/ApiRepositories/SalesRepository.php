<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISalesRepositoryInterface;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\Sales\SalesResource;
use App\Models\Customer;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class SalesRepository implements ISalesRepositoryInterface
{

    public function all()
    {
        return SalesResource::collection(Sale::with('sale_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return SalesResource::Collection(Sale::with('sale_details','update_notes','documents')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);

        $sale_details=$request->sale_details;

        $userId = Auth::id();
        $sales = new Sale();
        $sales->SaleNumber=$newInvoiceID;
        $sales->customer_id=$request->customer_id;
        $sales->SaleDate=$request->SaleDate;
        $sales->DueDate=$request->DueDate;
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
        $sales->createdDate=date('Y-m-d h:i:s');
        $sales->isActive=1;
        $sales->user_id = $userId ?? 0;
        $sales->save();
        $sales_id = $sales->id;

        foreach ($sale_details as $sale_item)
        {
            $data=SaleDetail::create([
                'sale_id'=>$sales_id,
                'PadNumber'=>$sale_item['PadNumber'],
                'vehicle_id'=>$sale_item['vehicle_id'],
                'product_id'=>$sale_item['product_id'],
                'unit_id'=>$sale_item['unit_id'],
                'Price'=>$sale_item['Price'],
                'Quantity'=>$sale_item['Quantity'],
                'rowTotal'=>$sale_item['rowTotal'],
                'VAT'=>$sale_item['VAT'],
                'rowVatAmount'=>$sale_item['rowVatAmount'],
                'rowSubTotal'=>$sale_item['rowSubTotal'],
                'Description'=>$sale_item['Description'],
            ]);
        }
        $Response = SalesResource::collection(Sale::where('id',$sales->id)->with('user','customer','sale_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function update(SaleRequest $saleRequest, $Id)
    {
        $userId = Auth::id();
        $saleRequest['user_id']=$userId ?? 0;

        $sale_detail=$saleRequest->sale_details;

        $sales = Sale::findOrFail($Id);
        $sales->customer_id=$saleRequest->customer_id;
        $sales->SaleDate=$saleRequest->SaleDate;
        $sales->DueDate=$saleRequest->DueDate;
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
        $sales->update();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'sales';
        $update_note->RelationId = $Id;
        $update_note->Description = $saleRequest->update_note;
        $update_note->user_id = $userId;
        $update_note->save();

        DB::table('sale_details')->where([['sale_id', $Id]])->delete();

        if(!empty($sale_detail))
        {
            foreach ($sale_detail as $sale_item)
            {
                $data=SaleDetail::create([
                    'sale_id'=>$Id,
                    'PadNumber'=>$sale_item['PadNumber'],
                    'vehicle_id'=>$sale_item['vehicle_id'],
                    'product_id'=>$sale_item['product_id'],
                    'Price'=>$sale_item['Price'],
                    'Quantity'=>$sale_item['Quantity'],
                    'rowTotal'=>$sale_item['rowTotal'],
                    'VAT'=>$sale_item['VAT'],
                    'rowVatAmount'=>$sale_item['rowVatAmount'],
                    'rowSubTotal'=>$sale_item['rowSubTotal'],
                    'Description'=>$sale_item['Description'],
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
        return array('products'=>Product::select('id','Name')->orderBy('id','desc')->get(),'customer'=>Customer::select('id','Name')->orderBy('id','desc')->get());
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
