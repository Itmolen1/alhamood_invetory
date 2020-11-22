<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    private $userResponse;
    private $IUserInformationRepository;

    public  function __construct(ServiceResponse $serviceResponse, \App\ApiRepositories\Interfaces\IUserRepositoryInterface $IUserInformationRepository)
    {
        $this->userResponse = $serviceResponse;
        $this->IUserInformationRepository = $IUserInformationRepository;
    }

    public function login()
    {
        try
        {
            return  $this->IUserInformationRepository->login();
        }
        catch (Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }
}
