<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User\UserResource;
use App\MISC\ServiceResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class UserRepository implements IUserRepositoryInterface
{
    protected $userResponse;
    public function __construct(ServiceResponse $serviceResponse)
    {
        $this->userResponse = $serviceResponse;
    }

    public function all()
    {
        // TODO: Implement all() method.
    }

    public function updateUser(Request $request)
    {
        // TODO: Implement updateUser() method.
    }

    public function updateUserImage(Request $request)
    {
        // TODO: Implement updateUserImage() method.
    }

    public function changePassword(Request $request)
    {
        // TODO: Implement changePassword() method.
    }

    public function ResetPassword(Request $request)
    {
        // TODO: Implement ResetPassword() method.
    }

    public function forgotPassword(Request $request)
    {
        // TODO: Implement forgotPassword() method.
    }

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            if ($user) {
                $accessToken = $user->createToken('MyApp')->accessToken;
                $users = new UserResource(User::all()->where('email', $user->email)->first());
//                if ($users->role == null)
//                {
//                    Return $this->userResponse->NotFoundRole();
//                }
//                else {
                    /*device token*/
                    $device_token=request('device_token');
                    $device_id=request('device_id');
                    $user_id=$users->id;
                    if($device_token!='' && $device_id!='')
                    {
                        $device_check = DB::table('token_master')->select('id')->where([['device_id',$device_id],['user_id',$user_id]])->first();
                        if(isset($device_check->id))
                        {
                            //update device token for existing device id
                            DB::table('token_master')->where([['device_id', $device_id],['user_id',$user_id]])->update(['device_token' =>$device_token]);
                        }
                        else
                        {
                            //add new device id and token for user
                            $data=array('user_id'=>$user_id,'device_token'=>$device_token,'device_id'=>$device_id,'created_at'=>date('Y-m-d h:i:s'),'updated_at'=>date('Y-m-d h:i:s'));
                            DB::table('token_master')->insert($data);
                        }
                    }
                    /*device token*/

                    //$UserToAuthorities = RoleResource::Collection(Role::all()->where('Id', $users->role_Id));
                    return $this->userResponse->LoginSuccess( $accessToken,$users,null ,'Login Successful');
                //}
            }
            else
            {
                Return $this->userResponse->LoginFailed();
            }
        }
        else
        {
            return $this->userResponse->LoginFailed();
        }
    }

    public function register(UserRequest $userRequest)
    {
        // TODO: Implement register() method.
    }

    public function details()
    {
        // TODO: Implement details() method.
    }

    public function delete($Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function logout(Request $request)
    {

        try
        {
            if (Auth::check()) {
                Auth::user()->token()->revoke();

                /*device token*/
                $device_id=$request['device_id'];
                $user_id=$request->id;
                if($user_id!='' && $device_id!='')
                {
                    $device_check = DB::table('token_master')->select('id')->where([['device_id',$device_id],['user_id',$user_id]])->first();
                    if(isset($device_check->id))
                    {
                        //remove device token for given device id
                        DB::table('token_master')->where([['device_id', $device_id],['user_id',$user_id]])->update(['device_token' =>NULL]);
                    }
                }
                /*device token*/

                return $this->userResponse->LogOut();
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong, failed to logOut');
            }
        }
        catch (Exception $ex)
        {
            return $this->userResponse->Exception($ex);
        }
    }
}
