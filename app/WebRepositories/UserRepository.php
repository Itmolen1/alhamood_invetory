<?php


namespace App\WebRepositories;


use App\Http\Requests\UserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository implements IUserRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(User::with('roles')->where('role_id','!=',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('users.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('users.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('role', function($data) {
                    return $data->roles->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                ])
                ->make(true);
        }
        $users = User::with('roles')->get();
        return view('admin.user.index',compact('users'));
    }

    public function store(UserRequest $userRequest)
    {
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($userRequest->hasFile('fileUpload'))
            $filename = $userRequest->file('fileUpload')->storeAs('profile', $filename,'public');
        else
            $filename = 'admin_assets/assets/images/users/default.png';

        $user = [
            'name' =>$userRequest->name,
            'email' =>$userRequest->email,
            'contactNumber' =>$userRequest->contactNumer,
            'company_id' =>$userRequest->company_id ?? 0,
            'address' =>$userRequest->address,
            'imageUrl' =>$filename,
            'password' =>bcrypt($userRequest->password),
            'role_id' =>$userRequest->roles,
        ];
        $user = User::create($user);
        //$user->roles()->attach($userRequest->roles);
        return redirect()->route('users.index');
    }

    public function update(Request $request, $Id)
    {
        $user = User::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('profile', $filename,'public');
        else
            $filename = $user->imageUrl;

        $user->name = $request->name;
        $user->company_id = $request->company_id ?? 0;
        $user->address = $request->address;
        $user->imageUrl = $filename ?? null;
        $user->contactNumber = $request->contactNumber;

        $user->save();
        $user->roles()->sync($request->roles);
        return redirect()->route('users.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function delete(Request $request, $Id)
    {
        $data = User::findOrFail($Id);
        $data->delete();
        return redirect()->route('users.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function create()
    {
        $roles = Role::all();
        $companies = Company::all();
        return view('admin.user.create',compact('roles','companies'));
    }

    public function edit($Id)
    {
        $user = User::with(['roles'])->where('id',$Id)->first();
        $roles = Role::all();
        $companies = Company::all();
        return view('admin.user.edit',compact('roles','companies','user'));
    }

    public function changePassword()
    {
        $user_id = session('user_id');
        $user = User::with(['roles'])->where('id',$user_id)->first();
        return view('admin.user.change_password',compact('user'));
    }

    public function UserUpdatePassword(Request $request, $Id)
    {
        $user = User::find($Id);
        $check  = Auth::guard('web')->attempt([
            'email' => $user->email,
            'password' => $request->current_password
        ]);
        if($check)
        {
            $user->password = bcrypt($request->password);
            $user->save();
            return redirect()->back()->with("success","Password changed successfully !");
        }
        else{
            return redirect()->back()->with("error","Your current password does not matches with the password you provided. Please try again.");
        }
    }
}
