<?php

namespace App\Http\Controllers;

use App\Http\Requests\StateRequest;
use App\Models\State;
use App\WebRepositories\Interfaces\IStatesRepositoryInterface;
use Illuminate\Http\Request;

class StateController extends Controller
{
    /**
     * @var IStatesRepositoryInterface
     */
    private $statesRepository;

    public function __construct(IStatesRepositoryInterface $statesRepository)
    {
        $this->statesRepository = $statesRepository;
    }

    public function index()
    {
        return $this->statesRepository->index();
    }


    public function create()
    {
        return $this->statesRepository->create();
    }


    public function store(StateRequest $stateRequest)
    {
        return $this->statesRepository->store($stateRequest);
    }


    public function show(State $state)
    {
        //
    }


    public function edit($Id)
    {
        return $this->statesRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->statesRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->statesRepository->delete($request, $Id);
    }
}
