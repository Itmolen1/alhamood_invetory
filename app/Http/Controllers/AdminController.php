<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $dashboard['total_users']=User::all()->count();
        $dashboard['total_sales_today']=Sale::all()->where('createdDate','=',date('Y-m-d'))->count();
        $dashboard['total_purchase_today']=Purchase::all()->where('createdDate','=',date('Y-m-d'))->count();
        //echo "<pre>";print_r($dashboard);die;
        return view('admin.index',compact('dashboard'));
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
