<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Sale;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    /**
     * @var IMeterReadingRepositoryInterface
     */
    private $meterReadingRepository;

    public function __construct(IMeterReadingRepositoryInterface $meterReadingRepository)
    {
        $this->meterReadingRepository = $meterReadingRepository;
    }

    public function index()
    {
        return $this->meterReadingRepository->index();
    }

    public function create()
    {
        return $this->meterReadingRepository->create();
    }


    public function store(Request $request)
    {
        return $this->meterReadingRepository->store($request);
    }

    public function show($Id)
    {
        //
    }

    public function edit($Id)
    {
        return $this->meterReadingRepository->edit($Id);
    }


    public function meterReadingUpdate(Request $request, $Id)
    {
        return $this->meterReadingRepository->update($request, $Id);
    }


    public function destroy(MeterReading $meterReading)
    {
        //
    }
}
