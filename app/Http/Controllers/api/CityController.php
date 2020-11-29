<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICityRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use App\MISC\ServiceResponse;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    private $cityRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ICityRepositoryInterface $cityRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->cityRepository=$cityRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->cityRepository->all());
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
            return $this->userResponse->Success($this->cityRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $result=$this->cityRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $city = City::find($id);
            if(is_null($city))
            {
                return $this->userResponse->Failed($city = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($city);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CityRequest $cityRequest, $id)
    {
        try
        {
            $city = City::find($id);
            if(is_null($city))
            {
                return $this->userResponse->Failed($city = (object)[],'Not Found.');
            }
            $result=$this->cityRepository->update($cityRequest,$id);
            return $this->userResponse->Success($result);;
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
            $city = City::find($Id);
            if(is_null($city))
            {
                return $this->userResponse->Failed($city = (object)[],'Not Found.');
            }
            $result = $this->cityRepository->delete($request,$Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = City::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->cityRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
