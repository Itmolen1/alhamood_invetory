<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * @var IRoleRepositoryInterface
     */
    private $roleRepository;

    public function  __construct(IRoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        return $this->roleRepository->index();
    }

    public function create()
    {
        return $this->roleRepository->create();
    }


    public function store(Request $request)
    {
        return $this->roleRepository->store($request);
    }


    public function show(Role $role)
    {
        //
    }


    public function edit($Id)
    {
        return $this->roleRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->roleRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->roleRepository->delete($request, $Id);
    }
}
