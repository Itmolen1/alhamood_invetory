<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use Illuminate\Http\Request;

class BankController extends Controller
{
    private $bankRepository;

    public function __construct(IBankRepositoryInterface $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

    public function index()
    {
        return $this->bankRepository->index();
    }


    public function create()
    {
        return $this->bankRepository->create();
    }


    public function store(BankRequest $bankRequest)
    {
        return $this->bankRepository->store($bankRequest);
    }


    public function show($Id)
    {
        return $this->bankRepository->getById($Id);
    }


    public function edit($Id)
    {
        return $this->bankRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->bankRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->bankRepository->delete($request, $Id);
    }
}
