<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

interface IUserRepositoryInterface
{
    public function all();

    public function updateUser(Request $request);

    public function updateUserImage(Request $request);

    public function changePassword(Request $request);

    public function ResetPassword(Request $request);

    public function forgotPassword(Request $request);

    public function login();

    public function register(UserRequest $userRequest);

    public function details();

    public function delete($Id);

    public function restore($Id);

    public function trashed();

    public function logout(Request $request);
}
