<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoginLog;
use App\Models\Receivable_summary_log;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        /* start of receivable summary logging*/
        $receivable_recorded=Receivable_summary_log::where('RecordDate',date('Y-m-d'))->where('company_id',session('company_id'))->get();
        if(!$receivable_recorded->first())
        {
            $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
                ->where('ac.customer_id','!=',0)
                ->where('ac.company_id',session('company_id'))
                ->groupBy('ac.customer_id')
                ->orderBy('ac.id','asc')
                ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
                ->get();
            $row=json_decode(json_encode($row), true);
            $needed_ids=array_column($row,'max_id');

            $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
                ->whereIn('ac.id',$needed_ids)
                ->orderBy('ac.id','asc')
                ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
                ->get();
            $row=json_decode(json_encode($row), true);
            foreach($row as $item)
            {
                Receivable_summary_log::create([
                    "company_id" => session('company_id'),
                    "customer_id" => $item['customer_id'],
                    "BalanceAmount"        => $item['Differentiate'],
                    "RecordDate"        => date('Y-m-d'),
                ]);
            }
        }
        /* end of receivable summary logging*/

        /* start of login logging */
        //$browserDetails = get_browser($request->header('User-Agent'), true);
        $sessionArray = array('userId'=>Auth::user()->id,
            'role'=>$user->role_id,
            'roleText'=>$user->roles->Name,
            'name'=>$user->name,
        );
        LoginLog::create([
            "company_id" => session('company_id'),
            "user_id" => Auth::user()->id,
            "sessionData" => json_encode($sessionArray),
            "machineIp" => $request->ip(),
            "userAgent" => $request->header('User-Agent'),
        ]);
        /* end of login logging */

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }
}
