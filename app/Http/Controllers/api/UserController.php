<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\MISC\ServiceResponse;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    private $userResponse;
    private $IUserRepository;

    public  function __construct(ServiceResponse $serviceResponse, IUserRepositoryInterface $IUserRepository)
    {
        $this->userResponse = $serviceResponse;
        $this->IUserRepository = $IUserRepository;
    }

    public function login()
    {
        try
        {
            return  $this->IUserRepository->login();
        }
        catch (Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function logout(Request $request)
    {
        return $this->IUserRepository->logout($request);
    }

    public function UserUpdate(Request $request)
    {
        return $this->IUserRepository->update($request);
    }

    public function UserChangePassword(Request $request)
    {
        return $this->IUserRepository->changePassword($request);
    }

    public function UserDetail($id)
    {
        return $this->IUserRepository->details($id);
    }

    public function destroy($Id)
    {
        return $this->IUserRepository->delete($Id);
    }

    public function UserUpdateProfilePicture(Request $request)
    {
        return $this->IUserRepository->UserUpdateProfilePicture($request);
    }
}
