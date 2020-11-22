<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use App\Models\Bank;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class BankController extends Controller
{
    private $userResponse;
    private $bankRepository;

    public function __construct(ServiceResponse $serviceResponse, IBankRepositoryInterface $bankRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->bankRepository=$bankRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->bankRepository->all());
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
            return $this->userResponse->Success($this->bankRepository->paginate($page_no,$page_size));
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
            $product = Bank::create($request->all());
            return $this->userResponse->Success($product);
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
            $product = Bank::find($id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($product);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function edit(Bank $bank)
    {
        //
    }

    public function update(Request $request,$id)
    {
        try
        {
            $product = Bank::find($id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            $product->update($request->all());
            $product->save();
            return $this->userResponse->Success($product);
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
            $product = $this->bankRepository->delete($request,$Id);
            return $this->userResponse->Success($product);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Bank::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->bankRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
