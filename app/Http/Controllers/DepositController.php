<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\WebRepositories\Interfaces\IDepositRepositoryInterface;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    private $depositRepository;

    public function __construct(IDepositRepositoryInterface $depositRepository)
    {
        $this->depositRepository = $depositRepository;
    }

    public function index()
    {
        return $this->depositRepository->index();
    }

    public function create()
    {
        return $this->depositRepository->create();
    }

    public function store(DepositRequest $depositRequest)
    {
        return $this->depositRepository->store($depositRequest);
    }

    public function edit($Id)
    {
        return $this->depositRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->depositRepository->update($request, $Id);
    }

    public function Deposit_delete($id)
    {
        return $this->depositRepository->delete($id);
    }
}
