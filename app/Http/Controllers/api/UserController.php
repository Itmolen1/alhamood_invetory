<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\Http\Controllers\Controller;
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
}
