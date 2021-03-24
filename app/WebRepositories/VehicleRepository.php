<?php


namespace App\WebRepositories;


use App\Http\Requests\VehicleRequest;
use App\Http\Resources\Vehicle\VehicleResource;
use App\Models\Customer;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use Illuminate\Http\Request;
use PDF;

class VehicleRepository implements IVehicleRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Vehicle::with('customer')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('vehicles.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('vehicles.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                    if($data->isActive == true)
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="vehicle_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="vehicle_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->addColumn('customerName', function($data) {
                    return $data->customer->Name ?? "";
                })
                ->addColumn('Description', function($data) {
                    return $data->Description ?? "a";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'customerName',
                    'Description',
                ])
                ->make(true);
        }
        //$vehicles = Vehicle::with('customer')->get();
        return view('admin.vehicle.index');
    }

    public function create()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.vehicle.create',compact('customers'));
    }

    public function store(VehicleRequest $vehicleRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $vehicle = [
            'registrationNumber' =>$vehicleRequest->registrationNumber,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'customer_id' =>$vehicleRequest->customer_id,
            'Description' =>$vehicleRequest->Description,
        ];
        Vehicle::create($vehicle);
        return redirect()->route('vehicles.create');
    }

    public function update(Request $request, $Id)
    {
        $vehicle = Vehicle::find($Id);

        $user_id = session('user_id');
        $vehicle->update([
            'registrationNumber' =>$request->registrationNumber,
            'user_id' =>$user_id,
            'customer_id' =>$request->customer_id,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('vehicles.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        $vehicle = Vehicle::with('customer')->find($Id);
        return view('admin.vehicle.edit',compact('customers','vehicle'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Vehicle::findOrFail($Id);
        $data->delete();
        return redirect()->route('vehicles.index');
    }

    public function CheckVehicleExist($request)
    {
        $data = Vehicle::where('registrationNumber','=',$request->registrationNumber)->where('customer_id','=',$request->customer_id)->get();
        if($data->first())
        {
            $result=array('result'=>true);
            return Response()->json(true);
        }
        else
        {
            $result=array('result'=>false);
            return Response()->json(false);
        }
    }

    public function ChangeVehicleStatus($Id)
    {
        $vehicle = Vehicle::find($Id);
        if($vehicle->isActive==1)
        {
            $vehicle->isActive=0;
        }
        else
        {
            $vehicle->isActive=1;
        }
        $vehicle->update();
        return Response()->json(true);
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function getVehicleList()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.vehicle.vehicle_report_by_customer',compact('customers'));
    }

    public function PrintVehicleList(Request $request)
    {
        if ($request->customer_id!='all')
        {
            $vehicles=Vehicle::with(['customer'=>function($q){$q->select('Name','id');}])->select('id','registrationNumber','customer_id')->get()->where('customer_id', '=', $request->customer_id);
        }
        else
        {
            $vehicles=Vehicle::with(['customer'=>function($q){$q->select('Name','id');}])->select('id','registrationNumber','customer_id')->where('company_id',session('company_id'))->get();
        }
        //echo "<pre>";print_r($vehicles);die;

        if(!$vehicles->isEmpty())
        {
            $row=json_decode(json_encode($vehicles), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER VEHICLE REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);


            $pdf::SetFont('helvetica', 'B', 8);
            if($row)
            {
                //for customer selection
                $customer_ids=array();
                $customer_name=array();
                foreach ($row as $item)
                {
                    $customer_ids[]=$item['customer']['id'];
                    $customer_name[]=$item['customer']['Name'];
                }
                $customer_ids=array_unique($customer_ids);
                $customer_name=array_unique($customer_name);
                $customer_ids=array_values($customer_ids);
                $customer_name=array_values($customer_name);
                //echo "<pre>";print_r($customer_name);die;
                for($i=0;$i<count($customer_ids);$i++)
                {
                    $customer_title='<u><b>'.'Customer :- '.$customer_name[$i].'</b></u>';
                    $pdf::SetFont('helvetica', 'B', 10);
                    $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('helvetica', '', 8);
                    //code will come here
                    $html = '<table border="0.5" cellpadding="3">
                    <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                        <th align="center" width="250">Vehicle</th>
                    </tr>';
                    for ($j=0;$j<count($row);$j++)
                    {
                        if ($customer_ids[$i]==$row[$j]['customer']['id'])
                        {
                            $html .= '<tr>
                                <td align="center" width="250">' . ($row[$j]['registrationNumber']) . '</td>
                                </tr>';
                        }
                    }
                    $pdf::SetFillColor(255, 0, 0);
                    $html .= '</table>';
                    //code will come here

                    $pdf::writeHTML($html, true, false, false, false, '');
                }
            }

            $pdf::lastPage();
            $time=time();
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
}
