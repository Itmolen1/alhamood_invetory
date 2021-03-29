<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\LoanMaster;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $dashboard['total_users']=User::all()->count();
        $dashboard['total_sales_today']=Sale::all()->where('createdDate','=',date('Y-m-d'))->count();
        $dashboard['total_purchase_today']=Purchase::all()->where('createdDate','=',date('Y-m-d'))->count();
        $admin=array();
        if(session('role_name')=='admin' || session('role_name')=='superadmin')
        {
            // getting latest closing for all customer from account transaction table
            $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
                ->where('ac.customer_id','!=',0)
                ->where('ac.company_id',session('company_id'))
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
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            // getting latest closing for all suppliers from account transaction table
            $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
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
            $row=json_decode(json_encode($row), true);
            $row=array_column($row,'Differentiate');
            $total_payable=array_sum($row);

            //total purchase quantity
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_sales_qty-$total_purchase_qty;

            //today's data
            $today_total_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('grandTotal');
            $today_credit_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('remainingBalance');
            $today_cash_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('paidBalance');

            $total_sales_qty=SaleDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_qty=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('rowSubTotal');
            $total_expense_amount=Expense::where('expenseDate','>=',date('Y-m-d').' 00:00:00')->where('expenseDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('grandTotal');

            $admin['today_total_sale']=$today_total_sale;
            $admin['today_credit_sale']=$today_credit_sale;
            $admin['today_cash_sale']=$today_cash_sale;

            $admin['today_sale_qty']=$total_sales_qty;
            $admin['today_purchase_qty']=$total_purchase_qty;
            $admin['today_purchase_amount']=$total_purchase_amount;
            $admin['today_expense_amount']=$total_expense_amount;

            $admin['total_receivable']=$total_receivable;
            $admin['total_payable']=$total_payable;
            $admin['stock_qty']=$stock_qty;
            $admin['cash_on_hand']=CashTransaction::select('Differentiate')->where('company_id',session('company_id'))->get()->last();
            $admin['loan_payable']=LoanMaster::where('loanType',1)->where('company_id',session('company_id'))->sum('inward_RemainingBalance');
            $admin['loan_receivable']=LoanMaster::where('loanType',0)->where('company_id',session('company_id'))->sum('outward_RemainingBalance');
        }
        return view('admin.index',compact('dashboard','admin'));
    }

    public function login()
    {
        return view('admin.user.login');
    }

    public  function register()
    {
         return view('admin.user.register');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
