<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IReportRepositoryInterface;
use App\Http\Resources\Expense\ExpenseResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Sales\SalesResource;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class ReportRepository implements IReportRepositoryInterface
{
    public function SalesReport(Request $request)
    {
        if($request->customer_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('customer_id',' =',$request->customer_id));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate));
        }
        else
        {
            return FALSE;
        }


        //echo "<pre>";print_r($data);die;
        if($sales)
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

//            $pdf::SetXY(25,7);
//            $pdf::SetFont('times', '', 12);
//            $pdf::MultiCell(83, 5, $company_title, 0, 'R', 0, 2, '', '', true, 0);
//            $pdf::SetFont('times', '', 8);
//
//            $pdf::SetXY(25,12);
//            $pdf::MultiCell(134, 5, $company_address, 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(44, 5, '', 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,16);
//            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,20);
//            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(71, 5, 'From Date : '.date('d-m-Y', strtotime($request->fromDate)), 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,24);
//            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(72, 5, 'To Date : '.date('d-m-Y', strtotime($request->toDate)), 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(28,28);
//            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
//            $pdf::SetXY(15,37);
//            $pdf::Ln(6);
//
//            $pdf::SetXY(25,35);
//            $pdf::writeHTML("<hr>", true, false, false, false, '');

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($sales), true);
            //echo "<pre>123";print_r($row);die;
            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('times', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">Invoice #</th>
                    <th align="center" width="70">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="40">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="60">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);
            for($i=0;$i<count($row);$i++)
            {
                $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['SaleNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_customer']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['api_vehicle']['registrationNumber']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Quantity']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Price']).'</td>
                    <td align="center" width="45">'.($row[$i]['sale_details'][0]['rowTotal']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['VAT']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['rowSubTotal']).'</td>
                    <td align="center" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="center" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';

            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
    }

    public function SalesReportByVehicle(Request $request)
    {
        if($request->vehicle_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate));
        }
        else
        {
            return FALSE;
        }
        //echo "<pre>";print_r($sales);die;

        if($sales)
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

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($sales), true);
            //echo "<pre>123";print_r($row);die;
            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('times', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">Invoice #</th>
                    <th align="center" width="70">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="40">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="60">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);
            for($i=0;$i<count($row);$i++)
            {
                if($request->vehicle_id!='')
                {
                    if(isset($row[$i]['sale_details'][0]['api_vehicle']['id']) && $row[$i]['sale_details'][0]['api_vehicle']['id']==$request->vehicle_id)
                    {
                        $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['SaleNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_customer']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['api_vehicle']['registrationNumber']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Quantity']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Price']).'</td>
                    <td align="center" width="45">'.($row[$i]['sale_details'][0]['rowTotal']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['VAT']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['rowSubTotal']).'</td>
                    <td align="center" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="center" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
                    </tr>';
                    }
                }
                else
                {
                        $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['SaleNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_customer']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['api_vehicle']['registrationNumber']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Quantity']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['Price']).'</td>
                    <td align="center" width="45">'.($row[$i]['sale_details'][0]['rowTotal']).'</td>
                    <td align="center" width="40">'.($row[$i]['sale_details'][0]['VAT']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['rowSubTotal']).'</td>
                    <td align="center" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="center" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
                    </tr>';
                }
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';

            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
    }

    public function PurchaseReport(Request $request)
    {
        if($request->customer_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id',' =',$request->supplier_id));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate));
        }
        else
        {
            return FALSE;
        }


        //echo "<pre>";print_r($data);die;
        if($purchase)
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

//            $pdf::SetXY(25,7);
//            $pdf::SetFont('times', '', 12);
//            $pdf::MultiCell(83, 5, $company_title, 0, 'R', 0, 2, '', '', true, 0);
//            $pdf::SetFont('times', '', 8);
//
//            $pdf::SetXY(25,12);
//            $pdf::MultiCell(134, 5, $company_address, 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(44, 5, '', 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,16);
//            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,20);
//            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(71, 5, 'From Date : '.date('d-m-Y', strtotime($request->fromDate)), 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(25,24);
//            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
//            $pdf::MultiCell(72, 5, 'To Date : '.date('d-m-Y', strtotime($request->toDate)), 0, 'R', 0, 2, '', '', true, 0);
//
//            $pdf::SetXY(28,28);
//            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
//            $pdf::SetXY(15,37);
//            $pdf::Ln(6);
//
//            $pdf::SetXY(25,35);
//            $pdf::writeHTML("<hr>", true, false, false, false, '');

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($purchase), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);


            $pdf::SetFont('times', '', 15);
            $html='PURCHASE REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="70">S.No.</th>
                    <th align="center" width="70">Vendor</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="50">Rate</th>
                    <th align="center" width="50">Total</th>
                    <th align="center" width="50">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="70">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);
            for($i=0;$i<count($row);$i++)
            {
                $html .='<tr>
                    <td align="center" width="70">'.($row[$i]['PurchaseNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_supplier']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['purchase_details'][0]['Quantity']).'</td>
                    <td align="center" width="50">'.($row[$i]['purchase_details'][0]['Price']).'</td>
                    <td align="center" width="50">'.($row[$i]['purchase_details'][0]['rowTotal']).'</td>
                    <td align="center" width="50">'.($row[$i]['purchase_details'][0]['VAT']).'</td>
                    <td align="center" width="50">'.($row[$i]['purchase_details'][0]['rowSubTotal']).'</td>
                    <td align="center" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="center" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="70">'.($row[$i]['PurchaseDate']).'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';

            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
    }

    public function ExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate));
        }
        else
        {
            return FALSE;
        }

        if($expense)
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

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($expense), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('times', '', 15);
            $html='Expenses';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="50">S.No.</th>
                    <th align="center" width="80">Employee</th>
                    <th align="center" width="60">Supplier</th>
                    <th align="center" width="40">Pad#</th>
                    <th align="center" width="80">Category</th>
                    <th align="center" width="50">Desc.</th>
                    <th align="center" width="40">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="70">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);
            for($i=0;$i<count($row);$i++)
            {
                $html .='<tr>
                    <td align="center" width="50">'.($row[$i]['expenseNumber']).'</td>
                    <td align="center" width="80">'.($row[$i]['api_employee']['Name']).'</td>
                    <td align="center" width="60">'.($row[$i]['api_supplier']['Name']).'</td>
                    <td align="center" width="40">'.($row[$i]['expense_details'][0]['PadNumber']).'</td>
                    <td align="center" width="80">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['expense_details'][0]['Description']).'</td>
                    <td align="center" width="40">'.($row[$i]['expense_details'][0]['Total']).'</td>
                    <td align="center" width="40">'.($row[$i]['expense_details'][0]['VAT']).'</td>
                    <td align="center" width="50">'.($row[$i]['expense_details'][0]['rowSubTotal']).'</td>
                    <td align="center" width="70">'.($row[$i]['expenseDate']).'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';

            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
    }

    public function CashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate);
        }
        else
        {
            return FALSE;
        }

        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage();$pdf::SetFont('times', '', 6);
        $pdf::SetFillColor(255,255,0);

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_cash_transactions), true);
        //echo "<pre>123";print_r($row);die;

        $pdf::SetFont('times', '', 15);
        $html='Cash Transactions';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('times', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

        $pdf::SetFont('times', 'B', 14);
        $html = '<table border="0" cellpadding="5">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="80">#</th>
                <th align="center" width="80">Date</th>
                <th align="center" width="100">Type</th>
                <th align="center" width="100">Details</th>
                <th align="center" width="60">Credit</th>
                <th align="center" width="60">Debit</th>
                <th align="center" width="60">Closing</th>

            </tr>';
        $pdf::SetFont('times', '', 10);
        for($i=0;$i<count($row);$i++)
        {
            $html .='<tr>
                <td align="center" width="80">'.($row[$i]['Reference']).'</td>
                <td align="center" width="80">'.($row[$i]['createdDate']).'</td>
                <td align="center" width="100">'.($row[$i]['Type']).'</td>
                <td align="center" width="100">N.A.</td>
                <td align="center" width="60">'.($row[$i]['Credit']).'</td>
                <td align="center" width="60">'.($row[$i]['Debit']).'</td>
                <td align="center" width="60"></td>
                </tr>';
        }
        $pdf::SetFillColor(255, 0, 0);
        $html.='</table>';

        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        //$url=url('/').'/storage/report_files/'.$time.'.pdf';
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }
}
