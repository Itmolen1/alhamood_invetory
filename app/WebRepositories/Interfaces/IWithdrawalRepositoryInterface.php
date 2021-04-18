<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\WithdrawalRequest;
use Illuminate\Http\Request;

interface IWithdrawalRepositoryInterface
{
    public function index();

    public function create();

    public function store(WithdrawalRequest $withdrawalRequest);

    public function update(Request $request, $Id);

    public function edit($Id);

    public function delete($Id);
}
