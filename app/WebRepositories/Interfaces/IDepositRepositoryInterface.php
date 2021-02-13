<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\DepositRequest;
use Illuminate\Http\Request;

interface IDepositRepositoryInterface
{
    public function index();

    public function create();

    public function store(DepositRequest $depositRequest);

    public function update(Request $request, $Id);

    public function edit($Id);
}
