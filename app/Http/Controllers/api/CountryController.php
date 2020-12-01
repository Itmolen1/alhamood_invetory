<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICountryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CountryRequest;
use App\MISC\ServiceResponse;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    private $countryRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ICountryRepositoryInterface $countryRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->countryRepository=$countryRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->countryRepository->all());
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
            return $this->userResponse->Success($this->countryRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $result=$this->countryRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $country = Country::find($id);
            if(is_null($country))
            {
                return $this->userResponse->Failed($country = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($country);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CountryRequest $countryRequest, $id)
    {
        try
        {
            $country = Country::find($id);
            if(is_null($country))
            {
                return $this->userResponse->Failed($country = (object)[],'Not Found.');
            }
            $result=$this->countryRepository->update($countryRequest,$id);
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
            $country = Country::find($Id);
            if(is_null($country))
            {
                return $this->userResponse->Failed($country = (object)[],'Not Found.');
            }
            $result = $this->countryRepository->delete($request,$Id);
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
            $restore = Country::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->countryRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $country = Country::find($Id);
            if(is_null($country))
            {
                return $this->userResponse->Failed($country = (object)[],'Not Found.');
            }
            $result=$this->countryRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
