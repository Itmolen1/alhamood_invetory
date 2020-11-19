<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\StateRequest;
use Illuminate\Http\Request;

interface IStatesRepositoryInterface
{

    public function index();

    public function create();

    public function store(StateRequest $stateRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
