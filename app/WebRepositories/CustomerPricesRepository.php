<?php


namespace App\WebRepositories;


use App\Models\Customer;
use App\Models\CustomerPrice;
use App\WebRepositories\Interfaces\ICustomerPricesRepositoryInterface;
use Illuminate\Http\Request;

class CustomerPricesRepository implements ICustomerPricesRepositoryInterface
{

    public function index()
    {
        $customers = Customer::with('customer_prices')->where(['isActive' => true,])->where('company_id',session('company_id'))->orderBy('id', 'Asc')->get();
        return view('admin.customer_prices.index',compact('customers'));
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(Request $request)
    {
        $count = count($request->input('Rate'));
        $user_id = session('user_id');
        $company_id = session('company_id');

        for($i=0;$i<count($request['customer_id']);$i++)
        {
            $customer_price = CustomerPrice::where('customer_id',$request['customer_id'][$i])->first();
            if($customer_price)
            {
                $customer_price->update(
                [
                    'customer_id'=>$request['customer_id'][$i],
                    'Rate'=>$request['Rate'][$i],
                    'VAT'=>$request['VAT'][$i],
                    'customerLimit'=>$request['customerLimit'][$i],
                    'Description'=>$request['Description'][$i],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);
            }
            else
            {
                CustomerPrice::create([
                        'customer_id'=>$request['customer_id'][$i],
                        'Rate'=>$request['Rate'][$i],
                        'VAT'=>$request['VAT'][$i],
                        'customerLimit'=>$request['customerLimit'][$i],
                        'Description'=>$request['Description'][$i],
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                ]);
            }
        }
        return redirect()->route('customer_prices.index')->with('update','Updated successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
