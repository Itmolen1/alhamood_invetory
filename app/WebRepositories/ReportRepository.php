<?php


namespace App\WebRepositories;


use App\Http\Resources\CustomerAdvance\CustomerAdvanceResource;
use App\Http\Resources\Expense\ExpenseResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Sales\SalesResource;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportRepository implements IReportRepositoryInterface
{
    public function GetBalanceSheet()
    {
        return view('admin.report.balance_sheet');
    }

    public function SalesReport()
    {
        return view('admin.report.sales_report');
    }

    public function PurchaseReport()
    {
        return view('admin.report.purchase_report');
    }

    public function ExpenseReport()
    {
        $expense_category= ExpenseCategory::get();
        return view('admin.report.expense_report',compact('expense_category'));
    }

    public function CashReport()
    {
        return view('admin.report.cash_report');
    }

    public function BankReport()
    {
        return view('admin.report.bank_report');
    }

    public function SalesReportByVehicle()
    {
        $vehicles = Vehicle::all();
        return view('admin.report.sales_report_by_vehicle',compact('vehicles'));
    }

    public function SalesReportByCustomer()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.sales_report_by_customer',compact('customers'));
    }

    public function PrintSalesReportByCustomer(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' &&  $request->customer_id!='all')
        {
            if($request->filter=='with')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '==', 0.00));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id));
            }
        }
        else
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate));
            }
        }
        //echo "<pre>";print_r($sales);die;

        if(!$sales->isEmpty())
        {
            $row=json_decode(json_encode($sales), true);

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

            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('times', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);


            $pdf::SetFont('times', 'B', 8);
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
                    $pdf::SetFont('times', 'B', 10);
                    $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('times', '', 8);
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
                                <td align="right" width="40">' . (number_format($current_vat_amount, 2, '.', '')) . '</td>
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
                             <td width="40" align="right">'. number_format($qty_sum, 2, '.', '') .'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="right">'. number_format($vat_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($sub_total_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($paid_total_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($balance_total_sum, 2, '.', '') .'</td>
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
                        $pdf::SetFont('times', 'B', 10);
                        $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                        $pdf::SetFont('times', '', 8);

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
                                <td align="right" width="40">' . (number_format($current_vat_amount, 2, '.', '')) . '</td>
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
                             <td width="40" align="right">'. number_format($qty_sum, 2, '.', '') .'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="right">'. number_format($vat_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($sub_total_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($paid_total_sum, 2, '.', '') .'</td>
                             <td width="50" align="right">'. number_format($balance_total_sum, 2, '.', '') .'</td>
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
                        $pdf::SetFont('times', 'B', 10);
                        $pdf::writeHTMLCell(0, 0, '', '', $veh_title,0, 1, 0, true, 'L', true);
                        $pdf::SetFont('times', '', 8);

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
                                <td align="center" width="140">' . ($row[$j]['api_customer']['Name']) . '</td>
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
                             <td width="40" align="right">'.number_format($qty_sum, 2, '.', '').'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="left">' . number_format($vat_sum, 2, '.', '') . '</td>
                             <td width="50" align="right">' . number_format($sub_total_sum, 2, '.', '') . '</td>
                             <td width="50" align="right">' . number_format($paid_total_sum, 2, '.', '') . '</td>
                             <td width="50" align="right">' . number_format($balance_total_sum, 2, '.', '') . '</td>
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

    public function PrintBankReport(Request $request)
    {
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

    public function PrintCashReport(Request $request)
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
                <td align="right" width="60">'.number_format($balance,2,'.',',').'</td>
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

    public function PrintExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->get()->where('expenseDate','>=',$request->fromDate)->where('expenseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->get()->where('expenseDate','>=',$request->fromDate)->where('expenseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->get()->where('expenseDate','>=',$request->fromDate)->where('expenseDate','<=',$request->toDate));
            }
        }
        else
        {
            return FALSE;
        }

        if($expense)
        {
            $row=json_decode(json_encode($expense), true);
            $cats_ids=array();
            $cats_name=array();
            foreach ($row as $item)
            {
                $cats_ids[]=$item['expense_details'][0]['api_expense_category']['id'];
                $cats_name[]=$item['expense_details'][0]['api_expense_category']['Name'];
            }
            $cats_ids=array_unique($cats_ids);
            $cats_name=array_unique($cats_name);
            //echo "<pre>123";print_r($cats);die;

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

            $pdf::SetFont('times', '', 15);
            $html='Expenses';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('times', '', 8);

            // if category is selected as all go for this code
            if($request->category==='all')
            {
                $html = '<table border="0.5" cellpadding="3">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">Date</th>
                    <th align="center" width="60">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="50">Category</th>
                    <th align="center" width="120">Vendor</th>
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
                    $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['expenseDate']).'</td>
                    <td align="center" width="60">'.($row[$i]['referenceNumber']).'</td>
                    <td align="center" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                    <td align="center" width="120">'.($row[$i]['api_supplier']['Name']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.','')).'</td>
                    <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.','')).'</td>
                    <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.','')).'</td>

                    </tr>';
                }
                $html.= '
                 <tr color="red">
                     <td width="420" align="right" colspan="5">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.','').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.','').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.','').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }
            else
            {
                for($i=0;$i<count($cats_ids);$i++)
                {
                    $category_name=$cats_name[$i];
                    $cat_title='<u><b>'.$category_name.'</b></u>';
                    $pdf::SetFont('times', '', 8);
                    $pdf::writeHTMLCell(0, 0, '', '', $cat_title,0, 1, 0, true, 'L', true);

                    $html = '<table border="0.5" cellpadding="3">
                        <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                            <th align="center" width="60">Date</th>
                            <th align="center" width="60">Expense#</th>
                            <th align="center" width="60">Employee</th>
                            <th align="center" width="120">Vendor</th>
                            <th align="center" width="70">TRN</th>
                            <th align="center" width="40">Taxable</th>
                            <th align="center" width="35">VAT</th>
                            <th align="center" width="45">NetTotal</th>
                        </tr>';
                    for($j=0;$j<count($row);$j++)
                    {
                        if($cats_ids[$i]==$row[$j]['expense_details'][0]['api_expense_category']['id'])
                        {
                            $total_sum+=$row[$j]['expense_details'][0]['Total'];

                            $sub_total_sum+=$row[$j]['expense_details'][0]['rowSubTotal'];
                            $this_row_vat_amount=$row[$j]['expense_details'][0]['Total']*$row[$j]['expense_details'][0]['VAT']/100;
                            $vat_sum+=$this_row_vat_amount;
                            $html .='<tr>
                                <td align="center" width="60">'.($row[$j]['expenseDate']).'</td>
                                <td align="center" width="60">'.($row[$j]['referenceNumber']).'</td>
                                <td align="center" width="60">'.($row[$j]['api_employee']['Name']).'</td>
                                <td align="center" width="120">'.($row[$j]['api_supplier']['Name']).'</td>
                                <td align="center" width="70">'.($row[$j]['api_supplier']['TRNNumber']).'</td>
                                <td align="right" width="40">'.(number_format($row[$j]['expense_details'][0]['Total'],2,'.','')).'</td>
                                <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.','')).'</td>
                                <td align="right" width="45">'.(number_format($row[$j]['expense_details'][0]['rowSubTotal'],2,'.','')).'</td>
                                </tr>';
                        }
                    }
                    $html.= '<tr color="red">
                             <td width="370" align="right" colspan="5">Total :</td>
                             <td width="40" align="right">'.number_format($total_sum,2,'.','').'</td>
                             <td width="35" align="right">'.number_format($vat_sum,2,'.','').'</td>
                             <td width="45" align="right">'.number_format($sub_total_sum,2,'.','').'</td>
                         </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html.='</table>';
                }
                //echo "<pre>";print_r($row);die;
                //<td align="center" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>

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
    }

    public function PrintPurchaseReport(Request $request)
    {
        if($request->customer_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('supplier_id',' =',$request->supplier_id));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            if($request->filter=='with')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
            }
        }
        else
        {
            return FALSE;
        }

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
            $row=json_decode(json_encode($purchase), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);


            $pdf::SetFont('times', '', 15);
            $html='PURCHASE REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $qty_sum=0.0;
            $rowTotal_sum=0.0;
            $VAT_sum=0.0;

            $pdf::SetFont('times', 'B', 10);
            $html = '<table border="0.5" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="right" width="60">S.No.</th>
                    <th align="center" width="70">Vendor</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="40">Rate</th>
                    <th align="center" width="50">Total</th>
                    <th align="center" width="45">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="70">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['purchase_details'][0]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['purchase_details'][0]['Quantity'];
                $rowTotal_sum+=$row[$i]['purchase_details'][0]['rowTotal'];
                $VAT_sum+=$row[$i]['purchase_details'][0]['rowTotal']*$row[$i]['purchase_details'][0]['VAT']/100;
                $html .='<tr>
                    <td align="right" width="60">'.($row[$i]['PurchaseNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_supplier']['Name']).'</td>
                    <td align="right" width="50">'.($row[$i]['purchase_details'][0]['Quantity']).'</td>
                    <td align="right" width="40">'.($row[$i]['purchase_details'][0]['Price']).'</td>
                    <td align="right" width="50">'.($row[$i]['purchase_details'][0]['rowTotal']).'</td>
                    <td align="right" width="45">'.(($row[$i]['purchase_details'][0]['rowTotal']*$row[$i]['purchase_details'][0]['VAT']/100)).'</td>
                    <td align="right" width="50">'.($row[$i]['purchase_details'][0]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="70">'.($row[$i]['PurchaseDate']).'</td>
                    </tr>';
            }
            $html.= '
             <tr color="red">
                 <td width="130" align="center" colspan="2">Total :- </td>
                 <td width="50" align="left">'.number_format($qty_sum,2,'.','').'</td>
                 <td width="40"></td>
                 <td width="50">'.number_format($rowTotal_sum,2,'.','').'</td>
                 <td width="45" align="left">'.number_format($VAT_sum,2,'.','').'</td>
                 <td width="50" align="left">'.number_format($sub_total_sum,2,'.','').'</td>
                 <td width="50" align="left">'.number_format($paid_total_sum,2,'.','').'</td>
                 <td width="50" align="left">'.number_format($balance_total_sum,2,'.','').'</td>
                 <td width="70" align="left"></td>
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
    }

    public function PrintSalesReportByVehicle(Request $request)
    {
        if($request->vehicle_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate));
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

            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">S.No.</th>
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
                        $sub_total_sum+=$row[$i]['sale_details'][0]['rowSubTotal'];
                        $paid_total_sum+=$row[$i]['paidBalance'];
                        $balance_total_sum+=$row[$i]['remainingBalance'];
                        $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['sale_details'][0]['PadNumber']).'</td>
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
                    $sub_total_sum+=$row[$i]['sale_details'][0]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['sale_details'][0]['PadNumber']).'</td>
                    <td align="center" width="70">'.($row[$i]['api_customer']['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['api_vehicle']['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['sale_details'][0]['Quantity']).'</td>
                    <td align="right" width="40">'.($row[$i]['sale_details'][0]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['sale_details'][0]['rowTotal']).'</td>
                    <td align="right" width="40">'.($row[$i]['sale_details'][0]['VAT']).'</td>
                    <td align="right" width="50">'.($row[$i]['sale_details'][0]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
                    </tr>';
                }
            }
            $html.= '
             <tr color="red">
                 <td width="60"></td>
                 <td width="70"></td>
                 <td width="50"></td>
                 <td width="40"></td>
                 <td width="40"></td>
                 <td width="45"></td>
                 <td width="40" align="left">Total : </td>
                 <td width="50" align="right">'.number_format($sub_total_sum,2,'.','').'</td>
                 <td width="50" align="right">'.number_format($paid_total_sum,2,'.','').'</td>
                 <td width="50" align="right">'.number_format($balance_total_sum,2,'.','').'</td>
                 <td width="60" align="right"></td>
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
    }

    public function PrintSalesReport(Request $request)
    {
        if($request->customer_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',date("y/m/d", strtotime($request->fromDate.' 23:59:59')))->where('SaleDate','<=',$request->toDate.' 23:59:59')->where('customer_id',' =',$request->customer_id));

        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->sortBy('sale_details.'));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->sortBy('sale_details.'));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->sortBy('sale_details.'));
            }
        }
        else
        {
            return FALSE;
        }

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

            $pdf::AddPage('L', 'A4');$pdf::SetFont('times', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($sales), true);
            //echo "<pre>";print_r($row);die;

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
                $master_row=array();
                $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'];
                $master_row['Name']=$row[$i]['api_customer']['Name'];
                $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'];
                $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'];
                $master_row['Price']=$row[$i]['sale_details'][0]['Price'];
                $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'];
                $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                $master_row['paidBalance']=$row[$i]['paidBalance'];
                $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                $master_row['SaleDate']=$row[$i]['SaleDate'];
                $new_master_array[]=$master_row;
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;


            //echo "<pre>123";print_r($row);die;
            $pdf::SetFont('times', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('times', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0.5" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">S.No.</th>
                    <th align="center" width="200">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="40">Rate</th>
                    <th align="center" width="55">Total</th>
                    <th align="center" width="50">VAT</th>
                    <th align="center" width="60">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="60">Date</th>

                </tr>';
            $pdf::SetFont('times', '', 10);

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
                $html .='<tr>
                    <td align="center" width="60">'.($row[$i]['PadNumber']).'</td>
                    <td align="center" width="200">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="50">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="40">'.($row[$i]['Price']).'</td>
                    <td align="right" width="55">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['VAT']).'</td>
                    <td align="right" width="60">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
                    </tr>';
            }

//            for($i=0;$i<count($row);$i++)
//            {
//                $sub_total_sum+=$row[$i]['sale_details'][0]['rowSubTotal'];
//                $paid_total_sum+=$row[$i]['paidBalance'];
//                $balance_total_sum+=$row[$i]['remainingBalance'];
//                $qty_sum+=$row[$i]['sale_details'][0]['Quantity'];
//                $rowTotal_sum+=$row[$i]['sale_details'][0]['rowTotal'];
//                $VAT_sum+=$row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100;
//                $rowSubTotal+=$row[$i]['sale_details'][0]['rowSubTotal'];
//                $html .='<tr>
//                    <td align="center" width="60">'.($row[$i]['sale_details'][0]['PadNumber']).'</td>
//                    <td align="center" width="200">'.($row[$i]['api_customer']['Name']).'</td>
//                    <td align="center" width="50">'.($row[$i]['sale_details'][0]['api_vehicle']['registrationNumber']).'</td>
//                    <td align="right" width="50">'.($row[$i]['sale_details'][0]['Quantity']).'</td>
//                    <td align="right" width="40">'.($row[$i]['sale_details'][0]['Price']).'</td>
//                    <td align="right" width="55">'.($row[$i]['sale_details'][0]['rowTotal']).'</td>
//                    <td align="right" width="50">'.(($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100)).'</td>
//                    <td align="right" width="60">'.($row[$i]['sale_details'][0]['rowSubTotal']).'</td>
//                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
//                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
//                    <td align="center" width="60">'.($row[$i]['SaleDate']).'</td>
//                    </tr>';
//            }

            $html.= '
                 <tr color="red">
                     <td width="60"></td>
                     <td width="200"></td>
                     <td width="50"></td>
                     <td width="50" align="right">'.number_format($qty_sum,2,'.','').'</td>
                     <td width="40"></td>
                     <td width="55" align="right">'.number_format($rowTotal_sum,2,'.','').'</td>
                     <td width="50" align="right">'.number_format($VAT_sum,2,'.','').'</td>
                     <td width="60" align="right">'.number_format($rowSubTotal,2,'.','').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.','').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.','').'</td>
                     <td width="60" align="right"></td>
                 </tr>';

            $html.='<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="60">S.No.</th>
                    <th align="center" width="200">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="40">Rate</th>
                    <th align="center" width="55">Total</th>
                    <th align="center" width="50">VAT</th>
                    <th align="center" width="60">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                    <th align="center" width="60">Date</th>

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
    }

    public function PrintBalanceSheet()
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
