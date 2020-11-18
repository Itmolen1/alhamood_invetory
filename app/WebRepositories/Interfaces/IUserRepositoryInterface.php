<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

interface IUserRepositoryInterface
{

    public function index();

    public function create();

    public function store(UserRequest $userRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();



}
