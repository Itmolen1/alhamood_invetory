<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\WebRepositories\Interfaces\IWithdrawalRepositoryInterface;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    private $withdrawalRepository;

    public function __construct(IWithdrawalRepositoryInterface $withdrawalRepository)
    {
        $this->withdrawalRepository = $withdrawalRepository;
    }

    public function index()
    {
        return $this->withdrawalRepository->index();
    }

    public function create()
    {
        return $this->withdrawalRepository->create();
    }

    public function store(WithdrawalRequest $withdrawalRequest)
    {
        return $this->withdrawalRepository->store($withdrawalRequest);
    }

    public function edit($Id)
    {
        return $this->withdrawalRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->withdrawalRepository->update($request, $Id);
    }

    public function Withdrawal_delete($id)
    {
        return $this->withdrawalRepository->delete($id);
    }
}
