<?php


namespace App\MISC;


class ServiceResponse
{
    public $statusCode;
    public $Message;
    public $Data;
    public $IsSuccess;

    public  function  Success($entity)
    {
        return Response([
            'StatusCode' => 200,
            'Message' => 'Success',
            'IsSuccess' => true,
            'Data' => $entity
        ], 200);
    }

    public  function  Failed($entity, $message)
    {
        return Response([
            'IsSuccess' => false,
            'StatusCode' => 401,
            'Message' => $message,
            'Data' => $entity,
        ], 200);
    }

    public  function Bad($entity)
    {
        return Response([
            'IsSuccess' => false,
            'Data' => [],
            'StatusCode' => 401,
            'Message' => $entity
        ], 200);
    }

    public  function Exception($entity)
    {
        return Response([
            'IsSuccess' => false,
            'Data' => [],
            'StatusCode' => 401,
            'Message' => $entity
        ], 200);

    }

    public  function LoginSuccess($token,$entity,$privileges,$Message)
    {
        return Response([
            'IsSuccess' => true,
            'Data' => $entity,
            'Token' => $token,
            'UserPrivileges' => $privileges,
            'StatusCode' => 200,
            'Message' => $Message
        ], 200);
    }

    public  function LoginFailed()
    {
        return Response([
            'IsSuccess' => False,
            'Data' => [],
            'Token' => [],
            'StatusCode' => 401,
            'Message' => 'Username or password incorrect'
        ], 200);
    }

    public  function NotFoundRole()
    {
        return Response([
            'IsSuccess' => False,
            'Data' => [],
            'Token' => [],
            'StatusCode' => 404,
            'Message' => 'Role not found'
        ], 200);
    }

    public  function Delete()
    {
        return Response([
            'IsSuccess' => true,
            'Data' => [],
            'StatusCode' => 200,
            'Message' => 'Data delete SuccessFull'
        ], 200);
    }

    public  function  UserAlreadyExist($entity)
    {
        return Response([
            'IsSuccess' => false,
            'Data' => $entity,
            'StatusCode' => 400,
            'Message' => 'user already Register'
        ], 200);
    }

    public function ValidationFailed($entity)
    {
        return Response([
            'IsSuccess' => false,
            'Data' => $entity,
            'StatusCode' => 403,
            'Message' => 'Validation Failed'
        ], 200);
    }

    public function LogOut()
    {
        return Response([
            'IsSuccess' => true,
            'Data' => [],
            'StatusCode' => 200,
            'Message' => 'LogOut SuccessFull'
        ], 200);
    }
}
