<?php


namespace App\WebRepositories;


use App\Http\Resources\AccountTransaction\AccountTransactionResource;
use App\Http\Resources\CustomerAdvance\CustomerAdvanceResource;
use App\Http\Resources\Expense\ExpenseResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Sales\SalesResource;
use App\Http\Resources\SupplierAdvance\SupplierAdvanceResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentReceive;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\Models\SupplierPayment;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportRepository implements IReportRepositoryInterface
{
    public function GetCustomerStatement()
    {
        return view('admin.report.customer_statement');
    }

    public function GetDetailCustomerStatement()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.customer_detailed_statement',compact('customers'));
    }

    public function GetSupplierStatement()
    {
        return view('admin.report.supplier_statement');
    }

    public function GetPaidAdvancesSummary()
    {
        return view('admin.report.paid_advance_summary');
    }

    public function GetReceivedAdvancesSummary()
    {
        return view('admin.report.received_advance_summary');
    }

    public function GetDetailSupplierStatement()
    {
        $suppliers = Supplier::where('company_id',session('company_id'))->get();
        return view('admin.report.supplier_detailed_statement',compact('suppliers'));
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
        $banks= Bank::get();
        return view('admin.report.bank_report',compact('banks'));
    }

    public function GeneralLedger()
    {
        return view('admin.report.general_ledger_report');
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
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('isActive', '!=', 0));
            }
        }
        else
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('isActive', '!=', 0));
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

    public function PrintGeneralLedger(Request $request)
    {
//        if ($request->fromDate!='' && $request->toDate!='')
//        {
//            $all_account_transactions=AccountTransactionResource::collection(AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Credit','!=',0.00)->where('Debit','!=',0.00)->where('Differentiate','!=',0.00));
//        }
//        else
//        {
//            return FALSE;
//        }

        //this is suppose to be generated with account_transaction table but something is not right in there so
        // generating this report manually

        //1.bring all cash sales -> debit
        //2.bring all cash purchase -> credit
        //3.all expenses (by cash and bank) -> credit
        //4.all customer advance (cash and bank) -> debit
        //5.all supplier advance (cash and bank) -> credit
        //6.all customer receive (cash and bank) -> debit
        //7.all supplier payment (cash and bank) -> credit

        //1.bring all cash sales -> debit
//        $row = Sale::select('PurchaseDate as Date', DB::raw('SUM(grandTotal) as PurchaseAmount'))
//            ->where('supplier_id','=',$supplier_id)
//            ->whereBetween('PurchaseDate',[$fromDate,$toDate])
//            ->groupBy('PurchaseDate')
//            ->get();
//        $row=json_decode(json_encode($row), true);

        //supplier payment entries
//        $row1 = SupplierPayment::select('transferDate as Date','paidAmount','referenceNumber','Description')
//            ->where('supplier_id','=',$supplier_id)
//            //->where('isPushed','=',1)
//            ->whereBetween('transferDate',[$fromDate,$toDate])
//            ->get();
//        $row1=json_decode(json_encode($row1), true);
//        $combined=array_merge($row,$row1);
//
//        $ord = array();
//        foreach ($combined as $key => $value){
//            $ord[] = strtotime($value['Date']);
//        }
//        array_multisort($ord, SORT_ASC, $combined);
//        //echo "<pre>123";print_r($combined);die;
//        $row=$combined;

        $all_account_transactions='';

        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_account_transactions), true);
        //echo "<pre>123";print_r($row);die;
        if(empty($row))
        {
            return FALSE;
        }

        $pdf::SetFont('helvetica', '', 15);
        $html='General Ledger';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="50">#</th>
                <th align="center" width="80">Date</th>
                <th align="center" width="100">Type</th>
                <th align="center" width="100">Details</th>
                <th align="center" width="60">Credit</th>
                <th align="center" width="60">Debit</th>
                <th align="center" width="60">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
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
                <td align="center" width="50">'.($row[$i]['referenceNumber']).'</td>
                <td align="center" width="80">'.($row[$i]['createdDate']).'</td>
                <td align="center" width="100">'.'Type'.'</td>
                <td align="center" width="100">N.A.</td>
                <td align="right" width="60">'.($row[$i]['Credit']).'</td>
                <td align="right" width="60">'.($row[$i]['Debit']).'</td>
                <td align="right" width="60">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
        }
        $html.= '
             <tr color="red">
                 <td width="50"></td>
                 <td width="80"></td>
                 <td width="100"></td>
                 <td width="100" align="right">Total : </td>
                 <td width="60" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="60" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="60" align="right">'.number_format($balance,2,'.',',').'</td>
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

    public function PrintBankReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_bank_transactions=BankTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('bank_id','=',$request->bank_id);
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

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_bank_transactions), true);
        $row=array_values($row);
        //echo "<pre>123";print_r($row);die;

        $pdf::SetFont('helvetica', '', 15);
        $html='Bank Name :-'.$request->bank_name;
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="80">Date</th>
                <th align="center" width="100">Type</th>
                <th align="center" width="100">Ref#</th>
                <th align="center" width="80">Debit</th>
                <th align="center" width="80">Credit</th>
                <th align="center" width="90">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
//            if($row[$i]['Debit']!=0)
//            {
//                $debit_total += $row[$i]['Debit'];
//                $balance = $balance + $row[$i]['Debit'];
//            }
//            else
//            {
//                $credit_total += $row[$i]['Credit'];
//                $balance = $balance - $row[$i]['Credit'];
//            }
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            //$balance = $balance + $row[$i]['Differentiate'];
            $html .='<tr>
                <td align="center" width="80">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="100">'.($row[$i]['Type']).'</td>
                <td align="center" width="100">'.$row[$i]['updateDescription'].'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                <td align="right" width="90">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        if($last_closing<0)
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="280" align="right" colspan="3">Total : </td>
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
            $html='<table border="0.5" cellpadding="0">';
            $html.= '
                 <tr>
                 <td width="280" align="right" colspan="3">Total : </td>
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

    public function PrintCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Details','not like','%hide%')->get();
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

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_cash_transactions), true);
        //echo "<pre>123";print_r($row);die;

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
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            $balance = $balance + $row[$i]['Differentiate'];
            if($i%2==0)
            {
                $html .='<tr style="background-color: #e3e3e3">
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="center" width="60">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="180">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            }
            else
            {
                $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="center" width="60">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="180">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            }
//            if($row[$i]['Debit']!=0)
//            {
//
//                $balance = $balance - $row[$i]['Debit'];
//            }
//            else
//            {
//                $credit_total += $row[$i]['Credit'];
//                $balance = $balance + $row[$i]['Credit'];
//            }
            $last_closing=$row[$i]['Differentiate'];
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

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;

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
                    <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                    <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                    <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>

                    </tr>';
                }
                $html.= '
                 <tr color="red">
                     <td width="420" align="right" colspan="5">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
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
                    $pdf::SetFont('helvetica', '', 8);
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
                                <td align="right" width="40">'.(number_format($row[$j]['expense_details'][0]['Total'],2,'.',',')).'</td>
                                <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                                <td align="right" width="45">'.(number_format($row[$j]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                                </tr>';
                        }
                    }
                    $html.= '<tr color="red">
                             <td width="370" align="right" colspan="5">Total :</td>
                             <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                             <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                             <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
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
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('supplier_id',' =',$request->supplier_id));
        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            if($request->filter=='with')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
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
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);
            $row=json_decode(json_encode($purchase), true);
            //echo "<pre>123";print_r($row);die;

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
                    <th align="center" width="30">LPO#</th>
                    <th align="right" width="30">PAD#</th>
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
                        <td align="center" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
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
                        <td align="center" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
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

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;
            $row=json_decode(json_encode($sales), true);
            //echo "<pre>123";print_r($row);die;
            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 15);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;

            $pdf::SetFont('helvetica', 'B', 14);
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
            $pdf::SetFont('helvetica', '', 10);
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
                    <td align="left" width="70">'.($row[$i]['api_customer']['Name']).'</td>
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
                    <td align="left" width="70">'.($row[$i]['api_customer']['Name']).'</td>
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
                 <td width="50" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                 <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
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
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->sortBy('sale_details.'));
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

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
                $master_row=array();
                $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'];
                $master_row['Name']=$row[$i]['api_customer']['Name'];
                $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
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
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="40"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="60" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

            $html.='<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="40">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="60">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
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

    public function PrintCustomerStatement()
    {
//        $row = DB::table('sales as s')->select('s.customer_id', DB::raw('SUM(s.remainingBalance) as SalesAmount'),'c.Name','c.Mobile')
//            ->where('remainingBalance','>=',10)
//            ->groupBy('customer_id')
//            ->orderBy('SalesAmount','desc')
//            ->leftjoin('customers as c', 'c.id', '=', 's.customer_id')
//            ->get();
//        $row=json_decode(json_encode($row), true);
        //$data=$row;
        //echo "<pre>";print_r($row);die;

        // getting latest closing for all customer from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.customer_id','!=',0)
            ->groupBy('ac.customer_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;

        //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER RECEIVABLE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Customer Name</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $total_balance+=$row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="left" width="300">'.($row[$i]['Name']).'</td>
                <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">TOTAL BALANCE : </td>
                     <td width="80" align="right">'.number_format($total_balance,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $data=CustomerAdvanceResource::collection(CustomerAdvance::get()->where('Amount','!=',0)->where('isPushed','=',1));
            if($data)
            {
                $pdf::SetFont('helvetica', '', 15);
                $html='CUSTOMER ADVANCES';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

                $row=json_decode(json_encode($data), true);
                //echo "<pre>";print_r($row);die;
                $pdf::SetFont('helvetica', '', 10);
                $html = '<table border="0.5" cellpadding="2">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="50">S.No</th>
                    <th align="center" width="300">Account</th>
                    <th align="center" width="100">Cell</th>
                    <th align="right" width="80">Balance</th>
                </tr>';

                $total_advances=0.0;
                for($j=0;$j<count($row);$j++)
                {
                    if($row[$i]['Differentiate']>0)
                    {
                        $total_advances += $row[$j]['Differentiate'];
                        $html .= '<tr>
                        <td align="center" width="50">' . ($j + 1) . '</td>
                        <td align="left" width="300">' . ($row[$j]['api_customer']['Name']) . '</td>
                        <td align="center" width="100">' . ($row[$j]['api_customer']['Mobile']) . '</td>
                        <td align="right" width="80">' . (number_format($row[$j]['Differentiate'], 2, '.', ',')) . '</td>
                        </tr>';
                    }
                }
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 13);
                $html='<table border="0" cellpadding="0">';
                $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">TOTAL ADVANCES : </td>
                     <td width="80" align="right">'.number_format($total_advances,2,'.',',').'</td>
                 </tr>';
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            $pdf::SetFont('helvetica', '', 12);
            $html='Receivable Total : '.number_format($total_balance,2,'.',',');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $html='Advances Total : '.number_format($total_advances,2,'.',',');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $html='Differance Total : '.number_format($total_balance-$total_advances,2,'.',',');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);


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

    public function PrintSupplierStatement()
    {
//        $row = DB::table('purchases as p')->select('p.supplier_id', DB::raw('SUM(p.remainingBalance) as PurchaseAmount'),'s.Name','s.Mobile')
//            ->groupBy('supplier_id')
//            ->orderBy('PurchaseAmount','desc')
//            ->leftjoin('suppliers as s', 's.id', '=', 'p.supplier_id')
//            ->get();
//        $row=json_decode(json_encode($row), true);

        // getting latest closing for all suppliers from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.supplier_id','!=',0)
            ->groupBy('ac.supplier_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;


        //$data=PurchaseResource::collection(Purchase::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;
            //$row=json_decode(json_encode($data), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('helvetica', '', 15);
            $html='SUPPLIER PAYABLE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']>0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.($i+1).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format($total_balance,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

//            $data=SupplierAdvanceResource::collection(SupplierAdvance::get()->where('Amount','!=',0)->where('isPushed','=',1));
//            if($data)
//            {
//                $pdf::SetFont('helvetica', '', 15);
//                $html='SUPPLIER ADVANCES';
//                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);
//
//                $row=json_decode(json_encode($data), true);
//                //echo "<pre>";print_r($row);die;
//                $pdf::SetFont('helvetica', '', 10);
//                $html = '<table border="0.5" cellpadding="2">
//                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
//                    <th align="center" width="50">S.No</th>
//                    <th align="center" width="300">Account</th>
//                    <th align="center" width="100">Cell</th>
//                    <th align="right" width="80">Balance</th>
//                </tr>';
//
//                $total_advances=0.0;
//                for($j=0;$j<count($row);$j++)
//                {
//                    $total_advances+=$row[$j]['Amount'];
//                    $html .='<tr>
//                    <td align="center" width="50">'.($j+1).'</td>
//                    <td align="left" width="300">'.($row[$j]['api_supplier']['Name']).'</td>
//                    <td align="center" width="100">'.($row[$j]['api_supplier']['Mobile']).'</td>
//                    <td align="right" width="80">'.(number_format($row[$j]['Amount'],2,'.',',')).'</td>
//                    </tr>';
//                }
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//
//                $pdf::SetFont('helvetica', 'B', 13);
//                $html='<table border="0" cellpadding="0">';
//                $html.= '
//                 <tr color="red">
//                     <td width="450" align="right" colspan="3">Total Advances : </td>
//                     <td width="80" align="right">'.number_format($total_advances,2,'.',',').'</td>
//                 </tr>';
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//            }
//
//            $pdf::SetFont('helvetica', '', 12);
//            $html='Outstanding Total : '.number_format($total_balance,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
//
//            $html='Advances Total : '.number_format($total_advances,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
//
//            $html='Differance Total : '.number_format($total_balance-$total_advances,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

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

    /*public function PrintDetailSupplierStatement(Request $request)
    {
        //get daily sum of grandTotal from purchases for the given supplier from date to date
        $supplier_id=$request->supplier_id;
        $fromDate=$request->fromDate;
        $toDate=$request->toDate;
        //purchase entries
        $row = Purchase::select('PurchaseDate as Date', DB::raw('SUM(grandTotal) as PurchaseAmount'))
            ->where('supplier_id','=',$supplier_id)
            ->whereBetween('PurchaseDate',[$fromDate,$toDate])
            ->groupBy('PurchaseDate')
            ->get();
        $row=json_decode(json_encode($row), true);

        //supplier payment entries
        $row1 = SupplierPayment::select('transferDate as Date','paidAmount','referenceNumber','Description')
            ->where('supplier_id','=',$supplier_id)
            //->where('isPushed','=',1)
            ->whereBetween('transferDate',[$fromDate,$toDate])
            ->get();
        $row1=json_decode(json_encode($row1), true);
        $combined=array_merge($row,$row1);

        $ord = array();
        foreach ($combined as $key => $value){
            $ord[] = strtotime($value['Date']);
        }
        array_multisort($ord, SORT_ASC, $combined);
        //echo "<pre>123";print_r($combined);die;
        $row=$combined;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Supplier Name : '.$request->supplier_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="200">Description</th>
                <th align="center" width="70">Debit</th>
                <th align="center" width="70">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $sum_of_differance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if(array_key_exists('PurchaseAmount',$row[$i]))
                {
                    //debit part = purchase entry
                    $sum_of_debit+=$row[$i]['PurchaseAmount'];
                    $sum_of_differance=$sum_of_differance+$row[$i]['PurchaseAmount'];
                    $html .='<tr>
                        <td align="center" width="60">'.($row[$i]['Date']).'</td>
                        <td align="left" width="70"></td>
                        <td align="left" width="200"></td>
                        <td align="right" width="70">'.(number_format($row[$i]['PurchaseAmount'],2,'.',',')).'</td>
                        <td align="right" width="70">'.(number_format(0.00,2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($sum_of_differance,2,'.',',')).'</td>
                        </tr>';
                }
                elseif(array_key_exists('paidAmount',$row[$i]))
                {
                    //credit part = supplier payment entry
                    $sum_of_credit+=$row[$i]['paidAmount'];
                    $sum_of_differance=$sum_of_differance-$row[$i]['paidAmount'];
                    $html .='<tr>
                        <td align="center" width="60">'.($row[$i]['Date']).'</td>
                        <td align="center" width="70">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="200">'.($row[$i]['Description']).'</td>
                        <td align="right" width="70">'.(number_format(0.00,2,'.',',')).'</td>
                        <td align="right" width="70">'.(number_format($row[$i]['paidAmount'],2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($sum_of_differance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($sum_of_differance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($sum_of_differance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($sum_of_differance,2,'.',',').'</td>
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
        else
        {
            return FALSE;
        }
    }*/

    public function PrintDetailSupplierStatement(Request $request)
    {
        //get daily sum of grandTotal from purchases for the given supplier from date to date
        $supplier_id=$request->supplier_id;
        $fromDate=$request->fromDate;
        $toDate=$request->toDate;

        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id','=',$request->supplier_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return FALSE;
        }
        //purchase entries
//        $row = Purchase::select('PurchaseDate as Date', DB::raw('SUM(grandTotal) as PurchaseAmount'))
//            ->where('supplier_id','=',$supplier_id)
//            ->whereBetween('PurchaseDate',[$fromDate,$toDate])
//            ->groupBy('PurchaseDate')
//            ->get();
//        $row=json_decode(json_encode($row), true);
//
//        //supplier payment entries
//        $row1 = SupplierPayment::select('transferDate as Date','paidAmount','referenceNumber','Description')
//            ->where('supplier_id','=',$supplier_id)
//            //->where('isPushed','=',1)
//            ->whereBetween('transferDate',[$fromDate,$toDate])
//            ->get();
//        $row1=json_decode(json_encode($row1), true);
//        $combined=array_merge($row,$row1);
//
//        $ord = array();
//        foreach ($combined as $key => $value){
//            $ord[] = strtotime($value['Date']);
//        }
//        array_multisort($ord, SORT_ASC, $combined);
//        //echo "<pre>123";print_r($combined);die;
//        $row=$combined;

        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;


        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Supplier Name : '.$request->supplier_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="200">Description</th>
                <th align="center" width="70">Debit</th>
                <th align="center" width="70">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($i==0)
                {
                    $closing_amount=$closing_amount+$row[$i]['Differentiate'];
                }
                else
                {
                    if($row[$i]['Debit']==0)
                    {
                        $closing_amount+=$row[$i]['Credit'];
                    }
                    else
                    {
                        $closing_amount-=$row[$i]['Debit'];
                    }
                }
                $sum_of_debit+=$row[$i]['Debit'];
                $sum_of_credit+=$row[$i]['Credit'];

                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="200">'.$row[$i]['Description'].'</td>
                    <td align="right" width="70">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="70">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($closing_amount<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($closing_amount,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($closing_amount,2,'.',',').'</td>
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
        else
        {
            return FALSE;
        }
    }

    public function PrintDetailCustomerStatement(Request $request)
    {
        //get daily sum of grandTotal from sales for the given customer from date to date
        $customer_id=$request->customer_id;
        $fromDate=$request->fromDate;
        $toDate=$request->toDate;

        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('customer_id','=',$request->customer_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return FALSE;
        }

        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Customer Name : '.$request->customer_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="200">Description</th>
                <th align="center" width="70">Debit</th>
                <th align="center" width="70">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($i==0)
                {
                    $closing_amount=$closing_amount+$row[$i]['Differentiate'];
                }
                else
                {
                    if($row[$i]['Debit']==0)
                    {
                        $closing_amount-=$row[$i]['Credit'];
                    }
                    else
                    {
                        $closing_amount+=$row[$i]['Debit'];
                    }
                }
                $sum_of_debit+=$row[$i]['Debit'];
                $sum_of_credit+=$row[$i]['Credit'];

                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="200">'.$row[$i]['Description'].'</td>
                    <td align="right" width="70">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="70">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($closing_amount<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($closing_amount,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($closing_amount,2,'.',',').'</td>
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
        else
        {
            return FALSE;
        }
    }

    public function PrintPaidAdvancesSummary()
    {
        // getting latest closing for all suppliers from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.supplier_id','!=',0)
            ->groupBy('ac.supplier_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='SUPPLIER ADVANCE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']<0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.($i+1).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format(abs($row[$i]['Differentiate']),2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format(abs($total_balance),2,'.',',').'</td>
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
            return FALSE;
        }
    }

    public function PrintReceivedAdvancesSummary()
    {
        // getting latest closing for all customers from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.customer_id','!=',0)
            ->groupBy('ac.customer_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER ADVANCE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']<0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.($i+1).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format(abs($row[$i]['Differentiate']),2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format(abs($total_balance),2,'.',',').'</td>
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
            return FALSE;
        }
    }
}
