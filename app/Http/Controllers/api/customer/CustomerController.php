<?php

namespace App\Http\Controllers\api\customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sales\SalesResource;
use App\MISC\ServiceResponse;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\PaymentReceive;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PDF;

class CustomerController extends Controller
{
    protected $userResponse;
    public function __construct(ServiceResponse $serviceResponse)
    {
        $this->userResponse = $serviceResponse;
    }

    public function customer_login(Request $request)
    {
        try
        {
            if($request['login_email']!='' and $request['password']!='')
            {
                $result=Customer::select('id','Name','login_email')->where('login_email','=',$request['login_email'])->where('password','=',md5($request['password']))->first();
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('Y-m-d h:i:s')));
                    return $this->userResponse->LoginSuccess( null ,$result,null ,'Login Successful');
                }
                else
                {
                    Return $this->userResponse->LoginFailed();
                }
            }
            else
            {
                Return $this->userResponse->LoginFailed();
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function customer_logout(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result = Customer::find($request['customer_id']);
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('0000-00-00 00:00:00')));
                    return $this->userResponse->LogOut();
                }
                else
                {
                    return $this->userResponse->Exception('Something is wrong, failed to logOut');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong, failed to logOut');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function customer_change_password(Request $request)
    {
        try
        {
            if($request['login_email']!='' and $request['currentPassword']!='' and $request['password']!='')
            {
                $result=Customer::select('id','Name','login_email')->where('login_email','=',$request['login_email'])->where('password','=',md5($request['currentPassword']))->first();
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('Y-m-d h:i:s'),'password'=>md5($request['password'])));
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Exception('email id or password not matching.');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_vehicles(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result=Vehicle::select('id','registrationNumber','isActive')->where('customer_id','=',$request['customer_id'])->get();
                if($result)
                {
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_purchase(Request $request,$page_no,$page_size)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('SaleDate', [$request['fromDate'], $request['toDate']])->with('sale_details')->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_purchase_by_vehicle(Request $request)
    {
        try
        {
            if($request['customer_id']!='' && $request['vehicle_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    if($item->sale_details[0]->vehicle->id==$request['vehicle_id'])
                    {
                        $final_array[]=$item;
                    }
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' && $request['vehicle_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('SaleDate', [$request['fromDate'], $request['toDate']])->with('sale_details')->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    if($item->sale_details[0]->vehicle->id==$request['vehicle_id'])
                    {
                        $final_array[]=$item;
                    }
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_account_status(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $amount=0.00;
                $customers=Customer::where('id',$request['customer_id'])->get();
                foreach ($customers as $customer)
                {
                    $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                    $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                    $amount=$debit_sum-$credit_sum;
                }
                return $this->userResponse->Success($amount);
            }
            else
            {
                return $this->userResponse->Exception('customer not found.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_payments(Request $request)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=PaymentReceive::select('id','paidAmount','amountInWords','payment_type','transferDate','ChequeNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=PaymentReceive::select('id','paidAmount','amountInWords','payment_type','transferDate','ChequeNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('transferDate', [$request['fromDate'], $request['toDate']])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_advances(Request $request)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=CustomerAdvance::select('id','Amount','sumOf','paymentType','TransferDate','ChequeNumber','spentBalance','remainingBalance')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=CustomerAdvance::select('id','Amount','sumOf','paymentType','TransferDate','ChequeNumber','spentBalance','remainingBalance')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('transferDate', [$request['fromDate'], $request['toDate']])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_account_statement(Request $request)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $account_transactions=AccountTransaction::where('customer_id','=',$request['customer_id'])->whereBetween('createdDate', [$request['fromDate'], $request['toDate']])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
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
                    $html='Account Statement ';
                    $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('helvetica', '', 12);
                    $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
                    $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                    $pdf::SetFont('helvetica', 'B', 14);
                    $html = '<table border="0.5" cellpadding="2">
                    <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                        <th align="center" width="100">Date</th>
                        <th align="center" width="150">Ref#</th>
                        <th align="center" width="80">Debit</th>
                        <th align="center" width="80">Credit</th>
                        <th align="right" width="100">Closing</th>
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
                    <td align="center" width="100">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="150">'.$row[$i]['referenceNumber'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="100">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
                    }
                    $html.='</table>';
                    $pdf::writeHTML($html, true, false, false, false, '');

                    $pdf::SetFont('helvetica', 'B', 13);

                    $html='<table border="0.5" cellpadding="0">';
                    $html.= '
                     <tr>
                         <td width="250" align="right" colspan="3">Total : </td>
                         <td width="80" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                         <td width="80" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                         <td width="100" align="right" color="red">'.number_format($closing_amount,2,'.',',').'</td>
                     </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html.='</table>';
                    $pdf::writeHTML($html, true, false, false, false, '');


                    $pdf::lastPage();

                    $time=time();
                    $fileLocation = storage_path().'/app/public/report_files/';
                    $fileNL = $fileLocation.'//'.$time.'.pdf';
                    $pdf::Output($fileNL, 'F');
                    $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
                    $url=array('url'=>$url);

                    return $this->userResponse->Success($url);
                }
                else
                {
                    return $this->userResponse->Failed($sales = (object)[],'NO RECORDS FOUND.');
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }
}
