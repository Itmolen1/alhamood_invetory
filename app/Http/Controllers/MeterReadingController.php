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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MeterReading  $meterReading
     * @return \Illuminate\Http\Response
     */
    public function show(MeterReading $meterReading)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MeterReading  $meterReading
     * @return \Illuminate\Http\Response
     */
    public function edit(MeterReading $meterReading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MeterReading  $meterReading
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MeterReading $meterReading)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MeterReading  $meterReading
     * @return \Illuminate\Http\Response
     */
    public function destroy(MeterReading $meterReading)
    {
        //
    }
}
