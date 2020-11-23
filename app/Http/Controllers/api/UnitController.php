<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IUnitRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use App\Models\Unit;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class UnitController extends Controller
{
    private $unitRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IUnitRepositoryInterface $unitRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->unitRepository=$unitRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->unitRepository->all());
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
            return $this->userResponse->Success($this->unitRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try
        {
            $unit = Unit::create($request->all());
            return $this->userResponse->Success($unit);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function show($id)
    {
        try
        {
            $unit = Unit::find($id);
            if(is_null($unit))
            {
                return $this->userResponse->Failed($unit = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($unit);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try
        {
            $unit = Unit::find($id);
            if(is_null($unit))
            {
                return $this->userResponse->Failed($unit = (object)[],'Not Found.');
            }
            $unit->update($request->all());
            $unit->save();
            return $this->userResponse->Success($unit);
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
            $unit = $this->unitRepository->delete($request,$Id);
            return $this->userResponse->Success($unit);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Unit::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->unitRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
