<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeterReaderRequest;
use App\MISC\ServiceResponse;
use App\Models\MeterReader;
use Illuminate\Http\Request;

class MeterReaderController extends Controller
{
    private $meterReaderRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IMeterReaderRepositoryInterface $meterReaderRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->meterReaderRepository=$meterReaderRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->meterReaderRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->meterReaderRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->meterReaderRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $meter = MeterReader::find($id);
            if(is_null($meter))
            {
                return $this->userResponse->Failed($meter = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($meter);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(MeterReaderRequest $meterReaderRequest, $id)
    {
        try
        {
            $meter = MeterReader::find($id);
            if(is_null($meter))
            {
                return $this->userResponse->Failed($meter = (object)[],'Not Found.');
            }
            return $this->meterReaderRepository->update($meterReaderRequest,$id);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $meter = MeterReader::find($Id);
            if(is_null($meter))
            {
                return $this->userResponse->Failed($meter = (object)[],'Not Found.');
            }
            $meter = $this->meterReaderRepository->delete($request,$Id);
            return $this->userResponse->Success($meter);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = MeterReader::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->meterReaderRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
