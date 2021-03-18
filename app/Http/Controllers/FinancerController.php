<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancerRequest;
use App\WebRepositories\Interfaces\IFinancerRepositoryInterface;
use Illuminate\Http\Request;

class FinancerController extends Controller
{
    private $financerRepository;

    public function __construct(IFinancerRepositoryInterface $financerRepository)
    {
        $this->financerRepository = $financerRepository;
    }

    public function index()
    {
        return $this->financerRepository->index();
    }

    public function create()
    {
        return $this->financerRepository->create();
    }

    public function store(FinancerRequest $financerRequest)
    {
        return $this->financerRepository->store($financerRequest);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        return $this->financerRepository->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->financerRepository->update($request, $id);
    }
}
