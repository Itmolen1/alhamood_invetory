<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{

    public function index()
    {
        return view('admin.driver.index');
    }


    public function create()
    {
        return view('admin.driver.create');
    }


    public function store(Request $request)
    {
        //
    }

    public function show(Driver $driver)
    {
        //
    }

    public function edit($edit)
    {
        return view('admin.driver.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Driver $driver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Driver $driver)
    {
        //
    }
}
