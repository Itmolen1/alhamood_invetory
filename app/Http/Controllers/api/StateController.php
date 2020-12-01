<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IStateRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StateRequest;
use App\MISC\ServiceResponse;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    private $stateRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IStateRepositoryInterface $stateRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->stateRepository=$stateRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->stateRepository->all());
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
            return $this->userResponse->Success($this->stateRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $result=$this->stateRepository->insert($request);
        return $this->userResponse->Success($result);;
    }

    public function show($id)
    {
        try
        {
            $state = State::find($id);
            if(is_null($state))
            {
                return $this->userResponse->Failed($state = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($state);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(StateRequest $stateRequest, $id)
    {
        try
        {
            $state = State::find($id);
            if(is_null($state))
            {
                return $this->userResponse->Failed($state = (object)[],'Not Found.');
            }
            $result=$this->stateRepository->update($stateRequest,$id);
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
            $state = State::find($Id);
            if(is_null($state))
            {
                return $this->userResponse->Failed($state = (object)[],'Not Found.');
            }
            $result = $this->stateRepository->delete($request,$Id);
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
            $restore = State::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->stateRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $state = State::find($Id);
            if(is_null($state))
            {
                return $this->userResponse->Failed($state = (object)[],'Not Found.');
            }
            $result=$this->stateRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
