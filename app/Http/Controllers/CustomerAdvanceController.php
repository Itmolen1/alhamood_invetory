<?php

namespace App\Http\Controllers;

use App\Models\CustomerAdvance;
use Illuminate\Http\Request;

class CustomerAdvanceController extends Controller
{

    public function index()
    {
        return view('admin.customerAdvance.index');
    }


    public function create()
    {
        return view('admin.customerAdvance.create');
    }


    public function store(Request $request)
    {
        //
    }


    public function show(CustomerAdvance $customerAdvance)
    {
        //
    }


    public function edit($Id)
    {
        return view('admin.customerAdvance.edit');
    }


    public function update(Request $request, CustomerAdvance $customerAdvance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerAdvance  $customerAdvance
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerAdvance $customerAdvance)
    {
        //
    }
}
