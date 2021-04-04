<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IBankToBankRepositoryInterface;
use Illuminate\Http\Request;

class BankToBankController extends Controller
{
    private $bankToBankRepository;

    public function __construct(IBankToBankRepositoryInterface $bankToBankRepository)
    {
        $this->bankToBankRepository = $bankToBankRepository;
    }
    public function index()
    {
        return $this->bankToBankRepository->index();
    }

    public function create()
    {
        return $this->bankToBankRepository->create();
    }

    public function store(Request $request)
    {
        return $this->bankToBankRepository->store($request);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        return $this->bankToBankRepository->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->bankToBankRepository->update($request, $id);
    }
}
