<?php

namespace App\Exceptions;

use App\MISC\ServiceResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {

        if ($request->expectsJson()) {
            $userResponse=new ServiceResponse();
            return Response([
                'Message' => 'Unauthenticated'
            ], 401);
            //return $userResponse->Bad(['error' => 'Unauthenticated.']);
//            echo "here";die;
//            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        else
        {
            return redirect()->route('login');
        }

        // return a plain 401 response even when not a json call
        return Response(['Message' => 'Unauthenticated'], 401);
    }


}
