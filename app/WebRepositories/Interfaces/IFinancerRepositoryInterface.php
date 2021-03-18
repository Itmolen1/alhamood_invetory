<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\FinancerRequest;
use Illuminate\Http\Request;

interface IFinancerRepositoryInterface
{
    public function index();

    public function create();

    public function store(FinancerRequest $financerRequest);

    public function update(Request $request, $id);

    public function getById($id);

    public function edit($id);

}
