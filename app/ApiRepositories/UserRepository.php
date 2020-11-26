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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

    public function update(Request $request)
    {
        $users = new User();
        try
        {
            $user = User::find($request->id);
            if(is_null($user))
            {
                return $this->userResponse->Failed($user = (object)[],'Not Found.');
            }
            $users->where('id', $request->id)->update(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'dateOfBirth' =>$request->dateOfBirth,
                    'contactNumber' =>$request->contactNumber,
                    'address' =>$request->address,
                    'gender_Id' =>$request->gender_Id,
                    'region_Id' =>$request->region_Id,
                ]);

            $users = new UserResource(User::all()->where('id', $request->id)->first());
            return $this->userResponse->Success($users);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function UserUpdateProfilePicture(Request $request)
    {
        try
        {
            $users = new User();
            if ($request->hasFile('imageUrl'))
            {
                $userId = Auth::id();

                //remove previously uploaded image first *will work in live server
                $image_val= DB::table('users')->select('imageUrl')->where([['id',$userId]])->first();
                $image_path = $_SERVER['DOCUMENT_ROOT']."/storage/app/public/images/".$image_val->imageUrl;
                if (file_exists($image_path)){
                    unlink($image_path);
                }
                //remove previously uploaded image first *will work in live server

                $file = $request->file('imageUrl');
                $extension = $file->getClientOriginalExtension();
                $filename=uniqid('user_').'.'.$extension;
                $request->file('imageUrl')->storeAs('profile', $filename,'public');
                $users->where('id', $userId)->update(['imageUrl' => $filename]);
                $users = new UserResource(User::all()->where('id', $userId)->first());
                return $this->userResponse->Success($users);
            }
            else
            {
                return $this->userResponse->Failed("user Image","file not found");
            }
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function changePassword(Request $request)
    {
        $users = new User();
        try
        {
            $credentials = $request->only(['email','currentPassword']);
            //$valid_user= DB::table('users')->select('id')->where([['email',$credentials['email']],['password',bcrypt($credentials['currentPassword'])]])->first();
            if (Auth::guard('client')->attempt($credentials))
            {
                return $this->userResponse->Failed($user = (object)[],"Invalid Credentials.");
            }
            else
            {
                echo "coming inside else";
            }
            die;
            $user = User::find($request->id);
            if(is_null($user))
            {
                return $this->userResponse->Failed($user = (object)[],'Not Found.');
            }
            $users=DB::table('users')->where([['email',$request->email],['password',$request->password]]);

            if ($user->where(['email' => request('email'), 'password' => request('currentPassword')]))
            {
                $users->where('id', $request->id)->update(['password' => bcrypt($request['password'])]);
                return $this->userResponse->Success('Password Update successfully');
            }
            else
            {
                return $this->userResponse->Success($request->id.'Current Password missed matched'.bcrypt($request->currentPassword));
            }
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
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

    public function details($id)
    {
        $user = User::find($id);
        if(is_null($user))
        {
            return $this->userResponse->Failed($user = (object)[],'Not Found.');
        }
        $users = new UserResource(User::all()->where('id', $user->id)->first());
        return $this->userResponse->Success($users);
    }

    public function delete($Id)
    {
        $user = User::withoutTrashed()->find($Id);
        if(is_null($user))
        {
            return $this->userResponse->Failed($user = (object)[],'Not Found.');
        }
        else
        {
            $user->delete();
            return $this->userResponse->Delete();
        }
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
