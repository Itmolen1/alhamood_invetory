<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IMeterReadingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeterReadingRequest;
use App\MISC\ServiceResponse;
use App\Models\MeterReading;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    private $meterReadingRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IMeterReadingRepositoryInterface $meterReadingRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->meterReadingRepository=$meterReadingRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->meterReadingRepository->all());
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
            return $this->userResponse->Success($this->meterReadingRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $expense=$this->meterReadingRepository->insert($request);
        return $this->userResponse->Success($expense);
    }

    public function show($id)
    {
        try
        {
            $expense = MeterReading::find($id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->meterReadingRepository->getById($id);
            return $this->userResponse->Success($expense);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }

    }

    public function update(MeterReadingRequest $meterReadingRequest, $id)
    {
        try
        {
            $expense = MeterReading::find($id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->meterReadingRepository->update($meterReadingRequest,$id);
            return $this->userResponse->Success($expense);
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
            $expense = MeterReading::find($Id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->meterReadingRepository->delete($request,$Id);
            return $this->userResponse->Success($expense);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = MeterReading::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->meterReadingRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function BaseList()
    {
        $data = $this->meterReadingRepository->BaseList();
        return $this->userResponse->Success($data);
    }
}
