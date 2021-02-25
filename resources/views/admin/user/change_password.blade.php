@extends('shared.layout-admin')
@section('title', 'Password Change')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Password Change</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Password Change</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">User</h4>
                        </div>
                        <div class="card-body">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <form method="post" action="{{ route('UserUpdatePassword',$user->id) }}" enctype="multipart/form-data" onsubmit="return matchPassword()">
                                @csrf
                                @method("PUT")
                                <div class="form-body">
                                    <h3 class="card-title">Change Your Password</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">User Name</label>
                                                <input type="text" id="name" name="name" value="{{ $user->name }}" class="form-control" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Email Address</label>
                                                <input type="text" id="email" name="email" value="{{ $user->email }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Current Password :- <span class="required">*</span></label>
                                                <input type="password" name="current_password" id="current_password"  placeholder="Enter Your Current Password" class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>New Password :- <span class="required">*</span></label>
                                                <input type="password" name="password" id="password" placeholder="Enter New Password" class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Re-Enter New Password :- <span class="required">*</span></label>
                                                <input type="password" name="password1" id="password1" placeholder="Re-Enter New Password"  class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Password</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {

        });

        function matchPassword() {
            var password = $('#password').val();
            var password1 = $('#password1').val();

            if(password != password1)
            {
                alert("Password and Re-Enter Password did not match");
                return false;
            }
            else
            {
                return true;
            }
        }
    </script>
@endsection
