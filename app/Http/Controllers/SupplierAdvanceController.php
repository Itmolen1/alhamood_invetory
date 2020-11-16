<?php

namespace App\Http\Controllers;

use App\Models\SupplierAdvance;
use Illuminate\Http\Request;

class SupplierAdvanceController extends Controller
{

    public function index()
    {
        return view('admin.supplierAdvance.index');
    }

    public function create()
    {
        return view('admin.supplierAdvance.create');
    }


    public function store(Request $request)
    {
        //
    }


    public function show(SupplierAdvance $supplierAdvance)
    {
        //
    }


    public function edit($Id)
    {
        return view('admin.supplierAdvance.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupplierAdvance  $supplierAdvance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupplierAdvance $supplierAdvance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SupplierAdvance  $supplierAdvance
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplierAdvance $supplierAdvance)
    {
        //
    }
}
