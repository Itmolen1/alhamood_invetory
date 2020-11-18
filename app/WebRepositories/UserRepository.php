<?php


namespace App\WebRepositories;


use App\Http\Requests\UserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use Illuminate\Http\Request;

class UserRepository implements IUserRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $users = User::with('roles')->get();
        return view('admin.user.index',compact('users'));
    }

    public function store(UserRequest $userRequest)
    {
        //dd($request->file('fileUpload'));
        // TODO: Implement store() method.
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($userRequest->hasFile('fileUpload'))
            $filename = $userRequest->file('fileUpload')->storeAs('profile', $filename,'public');

        else
            $filename = null;

        //dd($filename);

        $user = [
            'name' =>$userRequest->name,
            'email' =>$userRequest->email,
            'contactNumber' =>$userRequest->contactNumer,
            'company_id' =>$userRequest->company_id,
            'address' =>$userRequest->address,
            'imageUrl' =>$filename,
            'password' =>bcrypt($userRequest->password),
        ];
        $user = User::create($user);

            $user->roles()->attach($userRequest->roles);
            return redirect()->route('users.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $user = User::find($Id);

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('profile', $filename,'public');

        else
            $filename = $user->imageUrl;

        $user->name = $request->name;
        $user->company_id = $request->company_id;
        $user->address = $request->address;
        $user->imageUrl = $filename;
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
        // TODO: Implement delete() method.
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
        // TODO: Implement create() method.
        $roles = Role::all();
        $companies = Company::all();
        return view('admin.user.create',compact('roles','companies'));
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $user = User::with(['roles'])->where('id',$Id)->first();
        $roles = Role::all();
        $companies = Company::all();
        return view('admin.user.edit',compact('roles','companies','user'));
    }
}
