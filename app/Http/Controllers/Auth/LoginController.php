<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        $this->redirectTo = RouteServiceProvider::HOME;
    }

    public function showLoginForm()
    {
        if (Auth::check() == false)
            return view('admin.user.login');
        else
            return view('/');
    }

    protected function sendLoginResponse(Request $request)
    {
        $user=User::with('roles')->where('id',Auth::user()->id)->first();
        session(['user_id' => Auth::user()->id]);
        session(['company_id' => Auth::user()->company_id]);
        session(['role_id' => $user->role_id]);
        session(['role_name' => $user->roles->Name]);
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }
}
