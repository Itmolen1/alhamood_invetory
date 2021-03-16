<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IReportRepositoryInterface;
use App\Http\Resources\CustomerAdvance\CustomerAdvanceResource;
use App\Http\Resources\Expense\ExpenseResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Sales\SalesResource;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\CustomerAdvance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class ReportRepository implements IReportRepositoryInterface
{
    public function SalesReport(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if ($request->fromDate != '' && $request->toDate != '')
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            else
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->where('company_id',$company_id)->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());
            }
        }
        else
        {
            return false;
        }

        if($sales->first())
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);
            $row=array_values($row);

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['sale_details'][0]['PadNumber']!='0')
                {
                    $master_row=array();
                    $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                    $master_row['Name']=$row[$i]['api_customer']['Name'];
                    $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
                    $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                    $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                    $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                    $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                    $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                    $master_row['paidBalance']=$row[$i]['paidBalance'];
                    $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                    $master_row['SaleDate']=$row[$i]['SaleDate'];
                    $master_row['IsPaid']=$row[$i]['IsPaid'];
                    $new_master_array[]=$master_row;
                }
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;

            $pdf::SetFont('helvetica', '', 8);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 8);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 8);

            $VAT_sum=0.0;
            $rowTotal_sum=0.0;
            $qty_sum=0.0;
            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $rowSubTotal=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['Quantity'];
                $rowTotal_sum+=$row[$i]['rowTotal'];
                $VAT_sum+=$row[$i]['VAT'];
                $rowSubTotal+=$row[$i]['rowSubTotal'];
                if($row[$i]['IsPaid']==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
            }

            $html.= '
                 <tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

            $html.='<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='SALES_REPORT_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function SalesReportByVehicle(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if($request->vehicle_id=='all' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',$company_id)->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());

        }
        elseif ($request->fromDate!='' && $request->toDate!='' && $request->vehicle_id!='')
        {
            $ids=SaleDetail::where('vehicle_id','=',$request->vehicle_id)->where('company_id',$company_id)->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'sale_id');
            $sales=SalesResource::collection(Sale::with('sale_details')->whereIn('id', $ids)->where('company_id',$company_id)->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());
        }
        else
        {
            return FALSE;
        }

        if($sales->first())
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);
            $row=array_values($row);

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['sale_details'][0]['PadNumber']!='0')
                {
                    $master_row=array();
                    $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                    $master_row['Name']=$row[$i]['api_customer']['Name'];
                    $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
                    $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                    $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                    $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                    $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                    $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                    $master_row['paidBalance']=$row[$i]['paidBalance'];
                    $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                    $master_row['SaleDate']=$row[$i]['SaleDate'];
                    $master_row['IsPaid']=$row[$i]['IsPaid'];
                    $new_master_array[]=$master_row;
                }
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;

            $pdf::SetFont('helvetica', '', 8);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 8);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            if($request->vehicle_id==='all')
            {
                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['IsPaid']==1)
                    {
                        $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '<tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

                $html.='<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $vehicle_registrationNumber=Vehicle::select('registrationNumber')->where('id','=',$request->vehicle_id)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Vehicle : '.$vehicle_registrationNumber->registrationNumber;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['IsPaid']==1)
                    {
                        $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '<tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

                $html.='<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();

            $time=time();
            $name='SALES_REPORT_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return false;
        }
    }

    public function SalesReportByCustomerVehicle(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if ($request->fromDate!='' && $request->toDate!='' &&  $request->customer_id!='all')
        {
            if($request->filter=='with')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('isActive', '!=', 0));
            }
        }
        else
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',$company_id)->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('isActive', '!=', 0));
            }
        }
        //echo "<pre>";print_r($sales);die;

        if(!$sales->isEmpty())
        {
            $row=json_decode(json_encode($sales), true);
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);


            $pdf::SetFont('helvetica', 'B', 8);
            if($request->customer_id==='all')
            {
                //for customer selection
                $customer_ids=array();
                $customer_name=array();
                foreach ($row as $item)
                {
                    $customer_ids[]=$item['api_customer']['id'];
                    $customer_name[]=$item['api_customer']['Name'];
                }
                $customer_ids=array_unique($customer_ids);
                $customer_name=array_unique($customer_name);
                $customer_ids=array_values($customer_ids);
                $customer_name=array_values($customer_name);

                for($i=0;$i<count($customer_ids);$i++)
                {
                    $sub_total_sum=0.0;
                    $paid_total_sum=0.0;
                    $balance_total_sum=0.0;
                    $vat_sum=0.0;
                    $qty_sum=0.0;

                    $customer_title='<u><b>'.'Customer :- '.$customer_name[$i].'</b></u>';
                    $pdf::SetFont('helvetica', 'B', 10);
                    $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('helvetica', '', 8);
                    //code will come here
                    $html = '<table border="0.5" cellpadding="3">
                    <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                        <th align="center" width="60">S.No.</th>
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
                    for ($j=0;$j<count($row);$j++)
                    {
                        if ($customer_ids[$i]==$row[$j]['api_customer']['id'])
                        {
                            $sub_total_sum += $row[$j]['sale_details'][0]['rowSubTotal'];
                            $paid_total_sum += $row[$j]['paidBalance'];
                            $balance_total_sum += $row[$j]['remainingBalance'];
                            $current_vat_amount = $row[$j]['sale_details'][0]['rowTotal'] * $row[$j]['sale_details'][0]['VAT'] / 100;
                            $vat_sum += $current_vat_amount;
                            $qty_sum += $row[$j]['sale_details'][0]['Quantity'];
                            $html .= '<tr>
                                <td align="center" width="60">' . ($row[$j]['sale_details'][0]['PadNumber']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['api_vehicle']['registrationNumber']) . '</td>
                                <td align="right" width="40">' . ($row[$j]['sale_details'][0]['Quantity']) . '</td>
                                <td align="center" width="40">' . ($row[$j]['sale_details'][0]['Price']) . '</td>
                                <td align="center" width="45">' . ($row[$j]['sale_details'][0]['rowTotal']) . '</td>
                                <td align="right" width="40">' . (number_format($current_vat_amount, 2, '.', ',')) . '</td>
                                <td align="right" width="50">' . ($row[$j]['sale_details'][0]['rowSubTotal']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['paidBalance']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['remainingBalance']) . '</td>
                                <td align="center" width="60">' . ($row[$j]['SaleDate']) . '</td>
                                </tr>';
                        }
                    }
                    $html .= '
                         <tr color="red">
                             <td width="110" align="right" colspan="2">Total : </td>
                             <td width="40" align="right">'. number_format($qty_sum, 2, '.', ',') .'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="right">'. number_format($vat_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($sub_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($paid_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($balance_total_sum, 2, '.', ',') .'</td>
                             <td width="60" align="right"></td>
                         </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html .= '</table>';
                    //code will come here

                    $pdf::writeHTML($html, true, false, false, false, '');
                }
            }
            else
            {
                if($request->vehicle_id==='all')
                {
                    //for customer selection
                    $customer_ids=array();
                    $customer_name=array();
                    foreach ($row as $item)
                    {
                        $customer_ids[]=$item['api_customer']['id'];
                        $customer_name[]=$item['api_customer']['Name'];
                    }
                    $customer_ids=array_unique($customer_ids);
                    $customer_name=array_unique($customer_name);
                    $customer_ids=array_values($customer_ids);
                    $customer_name=array_values($customer_name);

                    for($i=0;$i<count($customer_ids);$i++)
                    {
                        $sub_total_sum=0.0;
                        $paid_total_sum=0.0;
                        $balance_total_sum=0.0;
                        $vat_sum=0.0;
                        $qty_sum=0.0;

                        $customer_title='<u><b>'.'Customer :- '.$customer_name[$i].'</b></u>';
                        $pdf::SetFont('helvetica', 'B', 10);
                        $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                        $pdf::SetFont('helvetica', '', 8);

                        //code will come here
                        $html = '<table border="0.5" cellpadding="3">
                        <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                            <th align="center" width="60">S.No.</th>
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
                        for ($j=0;$j<count($row);$j++)
                        {
                            if ($customer_ids[$i]==$row[$j]['api_customer']['id'])
                            {
                                $sub_total_sum += $row[$j]['sale_details'][0]['rowSubTotal'];
                                $paid_total_sum += $row[$j]['paidBalance'];
                                $balance_total_sum += $row[$j]['remainingBalance'];
                                $current_vat_amount = $row[$j]['sale_details'][0]['rowTotal'] * $row[$j]['sale_details'][0]['VAT'] / 100;
                                $vat_sum += $current_vat_amount;
                                $qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $html .= '<tr>
                                <td align="center" width="60">' . ($row[$j]['sale_details'][0]['PadNumber']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['api_vehicle']['registrationNumber']) . '</td>
                                <td align="right" width="40">' . ($row[$j]['sale_details'][0]['Quantity']) . '</td>
                                <td align="center" width="40">' . ($row[$j]['sale_details'][0]['Price']) . '</td>
                                <td align="center" width="45">' . ($row[$j]['sale_details'][0]['rowTotal']) . '</td>
                                <td align="right" width="40">' . (number_format($current_vat_amount, 2, '.', ',')) . '</td>
                                <td align="right" width="50">' . ($row[$j]['sale_details'][0]['rowSubTotal']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['paidBalance']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['remainingBalance']) . '</td>
                                <td align="center" width="60">' . ($row[$j]['SaleDate']) . '</td>
                                </tr>';
                            }
                        }
                        $html .= '
                         <tr color="red">
                             <td width="110" align="right" colspan="2">Total : </td>
                             <td width="40" align="right">'. number_format($qty_sum, 2, '.', ',') .'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="right">'. number_format($vat_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($sub_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($paid_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($balance_total_sum, 2, '.', ',') .'</td>
                             <td width="60" align="right"></td>
                         </tr>';
                        $pdf::SetFillColor(255, 0, 0);
                        $html .= '</table>';
                        //code will come here

                        $pdf::writeHTML($html, true, false, false, false, '');
                    }
                }
                else
                {
                    //for vehicle selection
                    $veh_ids=array();
                    $veh_name=array();
                    foreach ($row as $item)
                    {
                        if($item['sale_details'][0]['api_vehicle']['id']==$request->vehicle_id)
                        {
                            $veh_ids[]=$item['sale_details'][0]['api_vehicle']['id'];
                            $veh_name[]=$item['sale_details'][0]['api_vehicle']['registrationNumber'];
                        }
                    }
                    $veh_ids=array_unique($veh_ids);
                    $veh_name=array_unique($veh_name);

                    for($i=0;$i<count($veh_ids);$i++)
                    {
                        $sub_total_sum=0.0;
                        $paid_total_sum=0.0;
                        $balance_total_sum=0.0;
                        $vat_sum=0.0;
                        $qty_sum=0.0;

                        $vehicle_name=$veh_name[$i];
                        $veh_title='<u><b>'.'Vehicle :- '.$vehicle_name.'</b></u>';
                        $pdf::SetFont('helvetica', 'B', 10);
                        $pdf::writeHTMLCell(0, 0, '', '', $veh_title,0, 1, 0, true, 'L', true);
                        $pdf::SetFont('helvetica', '', 8);

                        $html = '<table border="0.5" cellpadding="3">
                        <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                            <th align="center" width="50">S.No.</th>
                            <th align="center" width="140">Customer</th>
                            <th align="center" width="40">Qty</th>
                            <th align="center" width="40">Rate</th>
                            <th align="center" width="45">Total</th>
                            <th align="center" width="40">VAT</th>
                            <th align="center" width="50">SubTotal</th>
                            <th align="center" width="50">Paid</th>
                            <th align="center" width="50">Balance</th>
                            <th align="center" width="50">Date</th>
                        </tr>';

                        for($j=0;$j<count($row);$j++)
                        {
                            if($veh_ids[$i]==$row[$j]['sale_details'][0]['api_vehicle']['id'])
                            {
                                $sub_total_sum += $row[$j]['sale_details'][0]['rowSubTotal'];
                                $paid_total_sum += $row[$j]['paidBalance'];
                                $balance_total_sum += $row[$j]['remainingBalance'];
                                $current_vat_amount=$row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100;
                                $vat_sum+=$current_vat_amount;
                                $qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $html .= '<tr>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['PadNumber']) . '</td>
                                <td align="left" width="140">' . ($row[$j]['api_customer']['Name']) . '</td>
                                <td align="right" width="40">' . ($row[$j]['sale_details'][0]['Quantity']) . '</td>
                                <td align="center" width="40">' . ($row[$j]['sale_details'][0]['Price']) . '</td>
                                <td align="center" width="45">' . ($row[$j]['sale_details'][0]['rowTotal']) . '</td>
                                <td align="center" width="40">' . ($current_vat_amount) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['rowSubTotal']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['paidBalance']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['remainingBalance']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['SaleDate']) . '</td>
                                </tr>';
                            }
                        }
                        $html .= '<tr color="red">
                             <td width="190" colspan="2">Total : </td>
                             <td width="40" align="right">'.number_format($qty_sum, 2, '.', ',').'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="left">' . number_format($vat_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($sub_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($paid_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($balance_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right"></td>
                        </tr>';
                        $pdf::SetFillColor(255, 0, 0);
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }
                    //for vehicle selection
                }
            }

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
        else
        {
            return FALSE;
        }
    }

    public function PurchaseReport(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if($request->supplier_id=='all' && $request->fromDate!='' && $request->toDate!='' && $request->filter=='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',$company_id)->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->supplier_id=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',$company_id)->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',$company_id)->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',$company_id)->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->supplier_id!='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',$company_id)->where('isActive','=',1)->where('supplier_id','=',$request->supplier_id)->whereBetween('PurchaseDate', [$request->fromDate, $request->toDate]));
        }
        else
        {
            return FALSE;
        }

        if($purchase->first())
        {
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);
            $row=json_decode(json_encode($purchase), true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 15);
            $html='PURCHASE REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $qty_sum=0.0;
            $rowTotal_sum=0.0;
            $VAT_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="30">PAD#</th>
                    <th align="right" width="30">LPO#</th>
                    <th align="center" width="110">Vendor</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="30">Rate</th>
                    <th align="center" width="55">Total</th>
                    <th align="center" width="45">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="55">Paid</th>
                    <th align="center" width="55">Balance</th>
                </tr>';
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['purchase_details_without_trash'][0]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['purchase_details_without_trash'][0]['Quantity'];
                $rowTotal_sum+=$row[$i]['purchase_details_without_trash'][0]['rowTotal'];
                $VAT_sum+=$row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100;
                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['PurchaseDate']))).'</td>
                        <td align="center" width="30">'.($row[$i]['purchase_details_without_trash'][0]['PadNumber']).'</td>
                        <td align="center" width="30">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['Quantity']).'</td>
                        <td align="right" width="30">'.($row[$i]['purchase_details_without_trash'][0]['Price']).'</td>
                        <td align="right" width="55">'.($row[$i]['purchase_details_without_trash'][0]['rowTotal']).'</td>
                        <td align="right" width="45">'.(($row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100)).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['rowSubTotal']).'</td>
                        <td align="right" width="55">'.($row[$i]['paidBalance']).'</td>
                        <td align="right" width="55">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['PurchaseDate']))).'</td>
                        <td align="center" width="30">'.($row[$i]['purchase_details_without_trash'][0]['PadNumber']).'</td>
                        <td align="center" width="30">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['Quantity']).'</td>
                        <td align="right" width="30">'.($row[$i]['purchase_details_without_trash'][0]['Price']).'</td>
                        <td align="right" width="55">'.($row[$i]['purchase_details_without_trash'][0]['rowTotal']).'</td>
                        <td align="right" width="45">'.(($row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100)).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['rowSubTotal']).'</td>
                        <td align="right" width="55">'.($row[$i]['paidBalance']).'</td>
                        <td align="right" width="55">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }

            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 8);
            $html='<table border="0.5" cellpadding="1">';
            $html.= '
             <tr color="red">
                 <td width="215" align="right" colspan="4">Total :- </td>
                 <td width="50" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 <td width="30"></td>
                 <td width="55" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                 <td width="45" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                 <td width="50" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 <td width="55" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                 <td width="55" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
             </tr>';
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
        else
        {
            return false;
        }
    }

    public function ExpenseReport(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->category=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->category=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter!='all' && $request->category!='all')
        {
            $ids=ExpenseDetail::where('expense_category_id','=',$request->category)->where('company_id',$company_id)->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',$company_id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($expense->first())
        {
            $row=json_decode(json_encode($expense), true);

            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Expenses';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            // if category is selected as all go for this code
            if($request->category==='all')
            {
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="60">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="50">Category</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }
            else
            {
                $category_name=ExpenseCategory::select('Name')->where('id','=',$request->category)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Category : '.$category_name->Name;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
                $pdf::SetFont('helvetica', '', 8);

                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="110">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }


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
        else{
            return false;
        }
    }

    public function CashReport(Request $request)
    {
        $user_id = Auth::id();
        $company_id = Str::getCompany($user_id);
        $request=(object)$request->all();
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('company_id',$company_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->where('Details','not like','%hide%')->orderBy('createdDate')->get();
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

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        $row=json_decode(json_encode($all_cash_transactions), true);

        $pdf::SetFont('helvetica', '', 15);
        $html='Cash Transactions';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="60">PAD/REF</th>
                <th align="center" width="180">Details</th>
                <th align="right" width="80">Debit</th>
                <th align="right" width="80">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['Debit']!=0)
            {
                $debit_total += $row[$i]['Debit'];
                $balance = $balance + $row[$i]['Debit'];
            }
            elseif($row[$i]['Credit']!=0)
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            else
            {
                $balance += $row[$i]['Differentiate'];
            }

            if($i%2==0)
            {
                $html .='<tr style="background-color: #e3e3e3">
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="60">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="180">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
                </tr>';
            }
            else
            {
                $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="60">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="180">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
                </tr>';
            }
            $last_closing=$balance;
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        if($last_closing<0)
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="300" align="right" colspan="2">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($last_closing,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }
        else
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="300" align="right" colspan="2">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($last_closing,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }

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

    public function BankReport(Request $request)
    {
        return false;
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_bank_transactions=BankTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate);
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
        $row=json_decode(json_encode($all_bank_transactions), true);
        //echo "<pre>123";print_r($row);die;

        $pdf::SetFont('times', '', 15);
        $html='Bank Transactions';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('times', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

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
            if($row[$i]['Debit']!=0)
            {
                $debit_total += $row[$i]['Debit'];
                $balance = $balance + $row[$i]['Debit'];
            }
            else
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            $html .='<tr>
                <td align="center" width="80">'.($row[$i]['Reference']).'</td>
                <td align="center" width="80">'.($row[$i]['createdDate']).'</td>
                <td align="center" width="100">'.($row[$i]['Type']).'</td>
                <td align="center" width="100">N.A.</td>
                <td align="right" width="60">'.($row[$i]['Credit']).'</td>
                <td align="right" width="60">'.($row[$i]['Debit']).'</td>
                <td align="right" width="60">'.number_format($balance,2,'.','').'</td>
                </tr>';
        }
        $html.= '
             <tr color="red">
                 <td width="80"></td>
                 <td width="80"></td>
                 <td width="100"></td>
                 <td width="100" align="right">Total : </td>
                 <td width="60" align="right">'.number_format($credit_total,2,'.','').'</td>
                 <td width="60" align="right">'.number_format($debit_total,2,'.','').'</td>
                 <td width="60" align="right">'.number_format($balance,2,'.','').'</td>
             </tr>';
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

    public function GetBalanceSheet()
    {
        $data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if($data)
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('times', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($data), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('times', '', 15);
            $html='Balance Sheet';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('times', '', 12);
            $html='Date :- '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="80">S.No</th>
                <th align="center" width="150">Account</th>
                <th align="center" width="150">Cell</th>
                <th align="right" width="150">Balance</th>
            </tr>';
            $pdf::SetFont('times', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $total_balance+=$row[$i]['remainingBalance'];
                $html .='<tr>
                <td align="center" width="80">'.($i+1).'</td>
                <td align="center" width="150">'.($row[$i]['api_customer']['Name']).'</td>
                <td align="center" width="150">'.($row[$i]['api_customer']['Mobile']).'</td>
                <td align="right" width="150">'.($row[$i]['remainingBalance']).'</td>
                </tr>';
            }
            $html.= '
                 <tr color="red">
                     <td width="80"></td>
                     <td width="150"></td>
                     <td width="150" align="right">Total Balance :- </td>
                     <td width="150" align="right">'.number_format($total_balance,2,'.','').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $data=CustomerAdvanceResource::collection(CustomerAdvance::get()->where('Amount','!=',0)->where('isPushed','=',1));
            if($data)
            {
                $pdf::SetFont('times', '', 15);
                $html='Advance Payments';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

                $row=json_decode(json_encode($data), true);
                //echo "<pre>";print_r($row);die;
                $pdf::SetFont('times', '', 10);
                $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="80">S.No</th>
                    <th align="center" width="150">Account</th>
                    <th align="center" width="150">Cell</th>
                    <th align="right" width="150">Balance</th>
                </tr>';


                $total_advances=0.0;
                for($j=0;$j<count($row);$j++)
                {
                    $total_advances+=$row[$j]['Amount'];
                    $html .='<tr>
                    <td align="center" width="80">'.($j+1).'</td>
                    <td align="center" width="150">'.($row[$j]['api_customer']['Name']).'</td>
                    <td align="center" width="150">'.($row[$j]['api_customer']['Mobile']).'</td>
                    <td align="right" width="150">'.($row[$j]['Amount']).'</td>
                    </tr>';
                }
                $html.= '
                 <tr color="red">
                     <td width="80"></td>
                     <td width="150"></td>
                     <td width="150" align="right">Total Advances :- </td>
                     <td width="150" align="right">'.number_format($total_advances,2,'.','').'</td>
                 </tr>';
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

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
        else
        {
            return FALSE;
        }
    }
}
