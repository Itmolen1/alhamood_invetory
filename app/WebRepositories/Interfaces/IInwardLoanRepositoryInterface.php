<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IInwardLoanRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $id);

    public function getById($id);

    public function edit($id);
}
