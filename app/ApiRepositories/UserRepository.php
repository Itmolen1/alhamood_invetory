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
use Illuminate\Support\Facades\Mail;
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
        return UserResource::collection(User::all()->sortDesc());
    }

    public function update(Request $request)
    {
        try
        {
            $user = Auth::guard('api')->user();
            if($user)
            {
                $user->name=$request->name;
                //$user->dateOfBirth=$request->dateOfBirth;
                $user->contactNumber=$request->contactNumber;
                //$user->address=$request->address;
                //$user->gender_Id=$request->gender_Id;
                //$user->region_Id=$request->region_Id;

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
                    //$user->where('id', $userId)->update(['imageUrl' => 'storage/app/public/profile/'.$filename]);
                    //$users = new UserResource(User::all()->where('id', $userId)->first());
                    //return $this->userResponse->Success($users);
                    $user->imageUrl='storage/app/public/profile/'.$filename;
                }


                $user->save();
                $userId = Auth::id();
                $users = new UserResource(User::all()->where('id', $userId)->first());
                return $this->userResponse->Success($users);
            }
            else
            {
                return $this->userResponse->Failed($user = (object)[],'Not Found.');
            }
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
                $users->where('id', $userId)->update(['imageUrl' => 'storage/app/public/profile/'.$filename]);
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
        try
        {
            $data = $request->all();
            $user = Auth::guard('api')->user();
            if( isset($data['currentPassword']) && !empty($data['currentPassword']) && $data['currentPassword'] !== "" && $data['currentPassword'] !=='undefined')
            {
                $check  = Auth::guard('web')->attempt([
                    'email' => $user->email,
                    'password' => $data['currentPassword']
                ]);
                if($check && isset($data['password']) && !empty($data['password']) && $data['password'] !== "" && $data['password'] !=='undefined')
                {
                    $user->password = bcrypt($data['password']);
                    $user->token()->revoke();
                    $accessToken = $user->createToken('MyApp')->accessToken;
                    $user->save();
                    return $this->userResponse->Success(['Token'=>$accessToken]);
                }
                else
                {
                    return $this->userResponse->Failed($user = (object)[],"Invalid Credentials.");
                }
            }
            else
            {
                return $this->userResponse->Failed($user = (object)[],"Invalid Credentials.");
            }
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function forgotPassword($id)
    {
        $user=User::where('id', $id)->first();
        $email=$user->email;
        if($email!='')
        {
            $six_digit_random_number = mt_rand(100000, 999999);
            $user->where('id', $user->id)->update(array('password'=>bcrypt($six_digit_random_number)));
            $to = $email;
            $subject = "ALHAMOOD FORGOT PASSWORD REQUEST";
            $txt = "Hello ".$user->name." your new password for login is : ".$six_digit_random_number." . please reset your password once you login.";
            $headers = "From: webmaster@example.com" . "\r\n";
            //mail($to,$subject,$txt,$headers);
            //return array('Message'=>'Password sent to your email address.');

            Mail::send('admin.city.index',array('message'=>$txt), function ($message) {
                $message->from('webmaster@example.com','ALHAMOOD');
                $message->to('inventory@wataninfotech.com');
                $message->subject('ALHAMOOD FORGOT PASSWORD REQUEST');
            });
        }
    }

    public function ResetPassword(Request $request)
    {
        // TODO: Implement ResetPassword() method.
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

    public function ActivateDeactivate($Id)
    {
        $user = User::find($Id);
        if($user->isActive==1)
        {
            $user->isActive=0;
        }
        else
        {
            $user->isActive=1;
        }
        $user->update();
        return new UserResource(User::find($Id));
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
