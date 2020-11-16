<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function index()
    {
        return view('admin.supplier.index');
    }


    public function create()
    {
        return view('admin.supplier.create');
    }


    public function store(Request $request)
    {
        //
    }


    public function show(Supplier $supplier)
    {
        //
    }


    public function edit($Id)
    {
        return view('admin.supplier.edit');
    }


    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    public function destroy(Supplier $supplier)
    {
        //
    }
}
