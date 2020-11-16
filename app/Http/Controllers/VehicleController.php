<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{

    public function index()
    {
        return view('admin.vehicle.index');
    }

    public function create()
    {
        return view('admin.vehicle.create');
    }


    public function store(Request $request)
    {
        //
    }


    public function show(Vehicle $vehicle)
    {
        //
    }


    public function edit($Id)
    {
        return view('admin.vehicle.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }
}
