<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\WebRepositories\Interfaces\IUnitRepositoryInterface;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * @var IUnitRepositoryInterface
     */
    private $unitRepository;

    public function __construct(IUnitRepositoryInterface $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function index()
    {
        return $this->unitRepository->index();
    }


    public function create()
    {
        //
    }


    public function store(UnitRequest $unitRequest)
    {
        return $this->unitRepository->store($unitRequest);
    }


    public function show(Unit $unit)
    {
        //
    }


    public function edit($Id)
    {
        return $this->unitRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->unitRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->unitRepository->delete($request, $Id);
    }
}
