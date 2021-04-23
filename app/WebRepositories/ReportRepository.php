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
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Financer;
use App\Models\Loan;
use App\Models\LoanMaster;
use App\Models\PaymentReceive;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Receivable_summary_log;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\Models\SupplierPayment;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use DateTime;
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
        $suppliers = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        return view('admin.report.supplier_detailed_statement',compact('suppliers'));
    }

    public function SalesReport()
    {
        return view('admin.report.sales_report');
    }

    public function PurchaseReport()
    {
        $suppliers = Supplier::where('company_type_id',2)->where('company_id',session('company_id'))->get();
        return view('admin.report.purchase_report',compact('suppliers'));
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

    public function Profit_loss()
    {
        return view('admin.report.profit_loss_report');
    }

    public function Garage_value()
    {
        return view('admin.report.garage_value_report');
    }

    public function SalesReportByVehicle()
    {
        $vehicles = Vehicle::where('company_id',session('company_id'))->whereNull('deleted_at')->get();
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
            $all_bank_transactions=BankTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('deleted_at','=',NULL)->where('bank_id','=',$request->bank_id)->orderBy('createdDate')->get();
            $prev_date = date('Y-m-d', strtotime($request->fromDate .' -1 day'));
            $get_max_id=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','=',$prev_date)->max('id');
            //echo "<pre>";print_r($get_max_id);die;
            $sum_of_debit_before_from_date=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','<',$request->fromDate)->sum('Credit');
            //$closing_amount=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('id',$get_max_id)->first();
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
//            if(!$closing_amount)
//            {
//                $closing_amount=0;
//            }
//            else{
//                $closing_amount=$closing_amount->Differentiate;
//            }
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
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=' Opening Balance : '.$closing_amount;
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=$closing_amount;
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

            //$balance = $balance + $row[$i]['Differentiate'];
            $html .='<tr>
                <td align="center" width="80">'.(date('d-M-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="100">'.($row[$i]['Type']).'</td>
                <td align="left" width="100">'.$row[$i]['updateDescription'].'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
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
                 <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
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
                 <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
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

    public function ViewBankReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_bank_transactions=BankTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('bank_id','=',$request->bank_id);
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($all_bank_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $title='Bank Name :-'.$request->bank_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Type</th>
                <th align="center">Ref#</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="center">Closing</th>
            </tr></thead><tbody>';
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            $html .='<tr>
                <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left">'.($row[$i]['Type']).'</td>
                <td align="center">'.$row[$i]['updateDescription'].'</td>
                <td align="right">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                <td align="right">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                <td align="right">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</tbody></table>';
        return view('admin.report.html_viewer',compact('html','title'))->render();
    }

    public function PrintCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Details','not like','%hide%')->orderBy('createdDate')->orderBy('id')->get();

            //$prev_date = date('Y-m-d', strtotime($request->fromDate .' -1 day'));
            //$get_max_id=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$prev_date)->max('id');
            //$closing_amount=CashTransaction::where('company_id',session('company_id'))->where('id',$get_max_id)->first();

            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
            //$closing_amount=$closing_amount->Differentiate;
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
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=' Opening Balance : '.$closing_amount;
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);


        $balance=0.0;
        $balance=$closing_amount;

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

    public function ViewCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('company_id',session('company_id'))->where('Details','not like','%hide%')->orderBy('createdDate')->get();
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }

        $row=json_decode(json_encode($all_cash_transactions), true);

        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }

        $title='CASH TRANSACTIONS : FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;
        $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">PAD/REF</th>
                <th align="center">Details</th>
                <th align="right">Debit</th>
                <th align="right">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            $balance = $balance + $row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="center">'.($row[$i]['PadNumber']).'</td>
                <td align="left">'.($row[$i]['Details']).'</td>
                <td align="right">'.($row[$i]['Debit']).'</td>
                <td align="right">'.($row[$i]['Credit']).'</td>
                <td align="right">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</tbody></table>';
        return view('admin.report.html_viewer',compact('html','title'))->render();
    }

    public function PrintExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->category=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->category=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->category!='all')
        {
            $ids=ExpenseDetail::where('expense_category_id','=',$request->category)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
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

    public function PrintPurchaseReport(Request $request)
    {
        if($request->supplier_id=='all' && $request->fromDate!='' && $request->toDate!='' && $request->filter=='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->supplier_id=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->supplier_id!='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('supplier_id','=',$request->supplier_id)->whereBetween('PurchaseDate', [$request->fromDate, $request->toDate]));
        }
        else
        {
            return FALSE;
        }

        if($purchase->first())
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

    public function PrintSalesReportByVehicle(Request $request)
    {
        if($request->vehicle_id=='all' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());

        }
        elseif ($request->fromDate!='' && $request->toDate!='' && $request->vehicle_id!='')
        {
            $ids=SaleDetail::where('vehicle_id','=',$request->vehicle_id)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'sale_id');
            $sales=SalesResource::collection(Sale::with('sale_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());
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
            //echo "<pre>";print_r($row);die;

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
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',session('company_id'))->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',session('company_id'))->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            else
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->where('deleted_at','=',NULL)->orderBy('SaleDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($sales->first())
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
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

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
//        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
//            ->where('ac.customer_id','!=',0)
//            ->where('ac.company_id',session('company_id'))
//            ->groupBy('ac.customer_id')
//            ->orderBy('ac.id','asc')
//            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
//            ->get();
//        $row=json_decode(json_encode($row), true);
//        $needed_ids=array_column($row,'max_id');
//
//        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
//            ->whereIn('ac.id',$needed_ids)
//            ->orderBy('ac.Differentiate','desc')
//            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
//            ->get();
//        $row=json_decode(json_encode($row), true);

        $result_array=array();
        $customers=Customer::where('company_id',session('company_id'))->get();
        foreach ($customers as $customer)
        {
            //get diff of total debit and credit column
            $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
            $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
            $diff=$debit_sum-$credit_sum;
            $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
            $result_array[]=$temp;
            unset($temp);
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);
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
                <th align="center" width="200">Customer Name</th>
                <th align="center" width="200">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
/*            for($i=0;$i<count($row);$i++)
            {
                $total_balance+=$row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="left" width="200">'.($row[$i]['Name']).'</td>
                <td align="left" width="200">'.($row[$i]['Mobile']).'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                </tr>';
            }*/
            for($i=0;$i<count($row);$i++)
            {
                $total_balance+=$row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="left" width="200">'.($row[$i]['Name']).'</td>
                <td align="left" width="200">'.($row[$i]['Mobile']).'</td>
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

            $total_advances=0.0;
//            $data=CustomerAdvanceResource::collection(CustomerAdvance::get()->where('Amount','!=',0)->where('isPushed','=',1));
//            if($data)
//            {
//                $pdf::SetFont('helvetica', '', 15);
//                $html='CUSTOMER ADVANCES';
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
//                    if($row[$j]['Differentiate']>0)
//                    {
//                        $total_advances += $row[$j]['Differentiate'];
//                        $html .= '<tr>
//                        <td align="center" width="50">' . ($j + 1) . '</td>
//                        <td align="left" width="300">' . ($row[$j]['api_customer']['Name']) . '</td>
//                        <td align="center" width="100">' . ($row[$j]['api_customer']['Mobile']) . '</td>
//                        <td align="right" width="80">' . (number_format($row[$j]['Differentiate'], 2, '.', ',')) . '</td>
//                        </tr>';
//                    }
//                }
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//
//                $pdf::SetFont('helvetica', 'B', 13);
//                $html='<table border="0" cellpadding="0">';
//                $html.= '
//                 <tr color="red">
//                     <td width="450" align="right" colspan="3">TOTAL ADVANCES : </td>
//                     <td width="80" align="right">'.number_format($total_advances,2,'.',',').'</td>
//                 </tr>';
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//            }
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

    function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
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
        /*$row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.supplier_id','!=',0)
            ->where('ac.company_id',session('company_id'))
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
        $row=json_decode(json_encode($row), true);*/
        //echo "<pre>";print_r($row);die;

        $result_array=array();
        $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        foreach ($suppliers as $supplier)
        {
            //get diff of total debit and credit column
            $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
            $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
            $diff=$credit_sum-$debit_sum;
            $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
            $result_array[]=$temp;
            unset($temp);
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);

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
            $account_transactions = AccountTransaction::orderBy('createdDate','asc')->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id','=',$request->supplier_id)->whereNull('updateDescription')->orderBy('createdDate','desc')->orderBy('id')->get();
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
                <th align="center" width="170">Description</th>
                <th align="center" width="80">Debit</th>
                <th align="center" width="80">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }

//                $sum_of_debit+=$row[$i]['Debit'];
//                $sum_of_credit+=$row[$i]['Credit'];

                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="170">'.$row[$i]['Description'].'</td>
                        <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="170">'.$row[$i]['Description'].'</td>
                        <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="300" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
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
                     <td width="300" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
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

    public function ViewDetailSupplierStatement(Request $request)
    {
        //get daily sum of grandTotal from purchases for the given supplier from date to date
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id','=',$request->supplier_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        else
        {
            $title='Supplier Name :-'.$request->supplier_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Ref#</th>
                <th align="center">Description</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';

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
                    <td align="left" width="170">'.$row[$i]['Description'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="90">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</tbody></table>';
            return view('admin.report.html_viewer',compact('html','title'))->render();
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
            $account_transactions=AccountTransaction::where('customer_id','=',$request->customer_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            //echo "<pre>123";print_r($account_transactions);die;
            //$data=Receivable_summary_log::with(['customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('RecordDate', [$request->fromDate, $request->toDate])->orderBy('RecordDate')->get();
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

    public function ViewDetailCustomerStatement(Request $request)
    {
        //get daily sum of grandTotal from sales for the given customer from date to date
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::oldest('createdDate')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('customer_id','=',$request->customer_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        else
        {
            $title='Customer Name :-'.$request->customer_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

            $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Ref#</th>
                <th align="center">Description</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';

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
                    <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left">'.$row[$i]['Description'].'</td>
                    <td align="right">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</tbody></table>';
            return view('admin.report.html_viewer',compact('html','title'))->render();
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

    public function PrintProfit_loss(Request $request)
    {
        if($request->month!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $company_name=Company::where('id',$company_id)->first();
            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='PROFIT AND LOSS REPORT '.date('M Y', strtotime($request->month));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Total Sales </td>
                     <td width="200" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Purchase </td>
                     <td width="200" align="right">'.number_format($total_purchase,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Expenses </td>
                     <td width="200" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Net Income </td>
                     <td width="200" align="right">'.number_format($total_sales-$total_purchase-$total_expense,2,'.',',').'</td>
                    </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintGarage_value(Request $request)
    {
        if($request->month!='' && $request->currentRate!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            //total receivable from customers
//            $total_receivable=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');

            $result_array=array();
            $customers=Customer::where('company_id',session('company_id'))->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            //cash in hand
//            $cash_in_hand=CashTransaction::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->max('id');
//            $lastTransaction = CashTransaction::where(['id'=> $cash_in_hand,])->get()->first();
//            $cash_in_hand=$lastTransaction->Differentiate;

            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->sum('Debit');
            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->sum('Credit');
            $cash_in_hand=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

            //sum of all bank balances
            $all_banks = Bank::where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
            $total_balance_in_bank=0.00;
            foreach($all_banks as $bank)
            {
                $last_transaction=BankTransaction::where('bank_id','=',$bank->id)->where('deleted_at','=',NULL)->max('id');
                $lastTransaction = BankTransaction::where(['id'=> $last_transaction,])->get()->first();
                $total_balance_in_bank+=$lastTransaction->Differentiate;
            }
            //stock value
                //total purchase quantity
                //$total_purchase_qty=PurchaseDetail::where('createdDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('createdDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
                $total_purchase_qty=PurchaseDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
                //total sales quantity
                $total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
                $stock_qty=$total_purchase_qty-$total_sales_qty;
                $stock_value=$stock_qty*$request->currentRate;

                //supplier outstanding
//            $total_supplier_outstanding=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');

            $result_array=array();
            $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_supplier_outstanding=array_sum($row);

            //loans
            //loan payable
            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->sum('inward_RemainingBalance');

            //loan receivable
            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->sum('outward_RemainingBalance');

            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $company_name=Company::where('id',$company_id)->first();
            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 15);
            $html='GARAGE VALUE REPORT '.date('M Y', strtotime($request->month));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Total Receivable </td>
                     <td width="200" align="right">'.number_format($total_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Cash +</td>
                     <td width="200" align="right">'.number_format($cash_in_hand,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Bank +</td>
                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Current Stock Value + <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="200" align="right">'.number_format($stock_value,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Receivable +</td>
                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Subtotal = </td>
                     <td width="200" align="right">'.number_format(($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value+$loan_receivable),2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Payable -</td>
                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
                    </tr>';

            $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value+$loan_receivable)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
                    </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time='Garage_Value_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;

        }
        else
        {
            return FALSE;
        }
    }

    public function GetSalesQuantitySummary()
    {
        return view('admin.report.get_sales_quantity_summary');
    }

    public function PrintSalesQuantitySummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $all_dates=array();
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $tmp_array=array('Date'=>$date,'Quantity'=>$qty);
                $final_array[]=$tmp_array;
            }

//            $final_array=array();
//            $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->where('deleted_at','=',NULL)->orderBy('SaleDate')->get());
//            $row=json_decode(json_encode($sales), true);
//            for($i=0;$i<count($row);$i++)
//            {
//                if($row[$i]['sale_details'][0]['Quantity']==25.00)
//                {
//                    $qty = $row[$i]['sale_details'][0]['id'];
//                    //$tmp_array=array('Date'=>'null','Quantity'=>$qty);
//                    $final_array[] = $qty;
//                }
//            }
//            $sales_ids=SaleDetail::where('Quantity','=',25)->where('company_id','=',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('deleted_at','=',NULL)->where('isActive','=',1)->get();
//            $sales_ids=json_decode(json_encode($sales_ids), true);
//            //echo "<pre>";print_r($final_array);die;
//
//            $sales_ids=array_column($sales_ids,'id');
//            echo "<pre>";print_r($sales_ids);die;
//            $sales_ids=array_diff($final_array,$sales_ids);
//            //$final_array=array_sum($final_array);
//            //$sales_ids=array_sum($sales_ids);
//            echo "<pre>";print_r($sales_ids);die;
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
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


            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='DAILY SALES QUANTITY REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Quantity</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Quantity'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='SALES_QTY_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetPurchaseQuantitySummary()
    {
        return view('admin.report.get_purchase_quantity_summary');
    }

    public function PrintPurchaseQuantitySummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $all_dates=array();
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $tmp_array=array('Date'=>$date,'Quantity'=>$qty);
                $final_array[]=$tmp_array;
            }
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
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


            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='DAILY PURCHASE QUANTITY REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Quantity</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Quantity'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='PURCHASE_QTY_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetDailyCashSummary()
    {
        return view('admin.report.get_daily_cash_summary');
    }

    public function PrintDailyCashSummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $sum_of_debit=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$date)->sum('Debit');
                $sum_of_credit=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$date)->sum('Credit');
                $amount=$sum_of_debit-$sum_of_credit;
                $tmp_array=array('Date'=>$date,'Amount'=>$amount);
                $final_array[]=$tmp_array;
            }
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
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


            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='DAILY CASH REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Amount</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Amount'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Amount'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Amount'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='DAILY_CASH_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    /* start analysis reports*/
    public function GetReceivableSummaryAnalysis()
    {
        return view('admin.report.get_receivable_summary_analysis');
    }

    public function ViewReceivableSummaryAnalysis(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $all_dates[]=$i->format("Y-m-d");
        }
        $data=Receivable_summary_log::with(['customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('RecordDate', [$request->fromDate, $request->toDate])->orderBy('RecordDate')->get();
        $data=json_decode(json_encode($data), true);
        $customers=Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        return view('admin.report.view_receivable_summary_analysis',compact('data','all_dates','customers'));
    }

    public function GetExpenseAnalysis()
    {
        return view('admin.report.get_expense_analysis');
    }

    public function ViewExpenseAnalysis(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        $all_expenses=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $date=$i->format("Y-m-d");
            $all_dates[]=$date=$i->format("Y-m-d");
            $expense=Expense::where('company_id',session('company_id'))->where('expenseDate',$date)->sum('grandTotal');
            $all_expenses[]=$expense;
        }
        $sum_of_expenses=array_sum($all_expenses);
        $average_of_expenses=$sum_of_expenses/count($all_expenses);
        return view('admin.report.view_expense_analysis',compact('all_expenses','all_dates','sum_of_expenses','average_of_expenses'));
    }

    public function GetExpenseAnalysisByCategory()
    {
        return view('admin.report.get_expense_analysis_by_category');
    }

    public function ViewExpenseAnalysisByCategory(Request $request)
    {
        $title='Category wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $expense_category=ExpenseCategory::all();
        $final_array=array();
        foreach($expense_category as $item)
        {
            $ids=ExpenseDetail::select('expense_id')->where('company_id',session('company_id'))->where('expense_category_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            $temp=Expense::where('company_id',session('company_id'))->whereIn('id',$ids)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'category_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_category',compact('final_array','sum_of_expenses','title'));
    }

    public function GetExpenseAnalysisByEmployee()
    {
        return view('admin.report.get_expense_analysis_by_employee');
    }

    public function ViewExpenseAnalysisByEmployee(Request $request)
    {
        $title='Employee wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $employees=Employee::where('company_id',session('company_id'))->get();
        $final_array=array();
        foreach($employees as $item)
        {
            $temp=Expense::where('company_id',session('company_id'))->where('employee_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'employee_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_employee',compact('title','final_array','sum_of_expenses'));
    }

    public function GetExpenseAnalysisBySupplier()
    {
        return view('admin.report.get_expense_analysis_by_supplier');
    }

    public function ViewExpenseAnalysisBySupplier(Request $request)
    {
        $title='Supplier wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $suppliers = Supplier::where('company_type_id','=',3)->where('company_id',session('company_id'))->get();
        $final_array=array();
        foreach($suppliers as $item)
        {
            $temp=Expense::where('company_id',session('company_id'))->where('supplier_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'supplier_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_supplier',compact('title','final_array','sum_of_expenses'));
    }

    /* end analysis reports */

    public function GetInwardLoanStatement()
    {
        $financers = Financer::where('company_id',session('company_id'))->get();
        return view('admin.report.inward_loan_statement',compact('financers'));
    }

    public function PrintInwardLoanStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->financer_id!='' )
        {
            //$account_transactions=AccountTransaction::where('customer_id','=',$request->customer_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $account_transactions=LoanMaster::where('loanType','=',1)->where('isPushed','=',1)->where('financer_id','=',$request->financer_id)->whereBetween('loanDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('loanDate')->orderBy('id')->get();
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
            $html='Financer Name : '.$request->financer_name;
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
                <th align="right" width="80">Amount</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
//                if($i==0)
//                {
                    $closing_amount=$closing_amount+$row[$i]['totalAmount'];
//                }
//                else
//                {
//                    if($row[$i]['Debit']==0)
//                    {
//                        $closing_amount-=$row[$i]['Credit'];
//                    }
//                    else
//                    {
//                        $closing_amount+=$row[$i]['Debit'];
//                    }
//                }
//                $sum_of_debit+=$row[$i]['Debit'];
//                $sum_of_credit+=$row[$i]['Credit'];

                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['loanDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="200">'.$row[$i]['Description'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['totalAmount'],2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);

            $html='<table border="0.5" cellpadding="0">';
            $html.= '
             <tr>
                 <td width="330" align="right" colspan="3">Total : </td>
                 <td width="80" align="right" color="red">'.number_format($closing_amount,2,'.',',').'</td>
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

    public function GetOutwardLoanStatement()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.outward_loan_statement',compact('customers'));
    }

    public function PrintOutwardLoanStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->customer_id!='' )
        {
            //$account_transactions=AccountTransaction::where('customer_id','=',$request->customer_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $account_transactions=LoanMaster::where('loanType','=',0)->where('isPushed','=',1)->where('customer_id','=',$request->customer_id)->whereBetween('loanDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('loanDate')->orderBy('id')->get();
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
                <th align="right" width="80">Amount</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
//                if($i==0)
//                {
                $closing_amount=$closing_amount+$row[$i]['totalAmount'];
//                }
//                else
//                {
//                    if($row[$i]['Debit']==0)
//                    {
//                        $closing_amount-=$row[$i]['Credit'];
//                    }
//                    else
//                    {
//                        $closing_amount+=$row[$i]['Debit'];
//                    }
//                }
//                $sum_of_debit+=$row[$i]['Debit'];
//                $sum_of_credit+=$row[$i]['Credit'];

                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['loanDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="200">'.$row[$i]['Description'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['totalAmount'],2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);

            $html='<table border="0.5" cellpadding="0">';
            $html.= '
             <tr>
                 <td width="330" align="right" colspan="3">Total : </td>
                 <td width="80" align="right" color="red">'.number_format($closing_amount,2,'.',',').'</td>
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
