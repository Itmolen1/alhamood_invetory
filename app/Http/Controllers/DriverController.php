<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverRequest;
use App\Models\Customer;
use App\Models\Driver;
use App\WebRepositories\Interfaces\IDriverRepositoryInterface;
use Illuminate\Http\Request;

class DriverController extends Controller
{

    private $driverRepository;

    public function __construct(IDriverRepositoryInterface $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    public function index()
    {
        return $this->driverRepository->index();
    }


    public function create()
    {
        return $this->driverRepository->create();
    }


    public function store(DriverRequest $driverRequest)
    {
        return $this->driverRepository->store($driverRequest);
    }

    public function show(Driver $driver)
    {
        //
    }

    public function edit($Id)
    {
        return $this->driverRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->driverRepository->update($request, $Id);
    }

    public function destroy(Driver $driver)
    {
        //
    }
}
