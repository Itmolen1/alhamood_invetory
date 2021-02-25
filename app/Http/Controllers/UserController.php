<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(IUserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return $this->userRepository->index();
    }

    public function create()
    {
        return $this->userRepository->create();
    }


    public function store(UserRequest $userRequest)
    {
        return $this->userRepository->store($userRequest);
    }

    public function show($id)
    {
        //
    }

    public function edit($Id)
    {
        return $this->userRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->userRepository->update($request, $Id);
    }

    public function UserChangePassword()
    {
        return $this->userRepository->changePassword();
    }

    public function UserUpdatePassword(Request $request, $Id)
    {
        return $this->userRepository->UserUpdatePassword($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return$this->userRepository->delete($request, $Id);
    }
}
