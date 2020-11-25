<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReaderRequest;
use App\Models\MeterReader;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use Illuminate\Http\Request;

class MeterReaderController extends Controller
{
    /**
     * @var IMeterReaderRepositoryInterface
     */
    private $meterReaderRepository;

    public function __construct(IMeterReaderRepositoryInterface $meterReaderRepository)
   {
       $this->meterReaderRepository = $meterReaderRepository;
   }

    public function index()
    {
        return $this->meterReaderRepository->index();
    }


    public function create()
    {
        //
    }


    public function store(MeterReaderRequest $meterReaderRequest)
    {
        return $this->meterReaderRepository->store($meterReaderRequest);
    }


    public function show($Id)
    {
        //
    }

    public function edit($Id)
    {
        return $this->meterReaderRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->meterReaderRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->meterReaderRepository->delete($request, $Id);
    }
}
