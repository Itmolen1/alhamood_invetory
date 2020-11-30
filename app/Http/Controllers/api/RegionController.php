<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IRegionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\MISC\ServiceResponse;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    private $regionRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IRegionRepositoryInterface $regionRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->regionRepository=$regionRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->regionRepository->all());
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
            return $this->userResponse->Success($this->regionRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $result=$this->regionRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $region = Region::find($id);
            if(is_null($region))
            {
                return $this->userResponse->Failed($region = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($region);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(RegionRequest $regionRequest, $id)
    {
        try
        {
            $region = Region::find($id);
            if(is_null($region))
            {
                return $this->userResponse->Failed($region = (object)[],'Not Found.');
            }
            $result=$this->regionRepository->update($regionRequest,$id);
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
            $region = Region::find($Id);
            if(is_null($region))
            {
                return $this->userResponse->Failed($region = (object)[],'Not Found.');
            }
            $result = $this->regionRepository->delete($request,$Id);
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
            $restore = Region::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->regionRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
