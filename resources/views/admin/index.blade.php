@extends('shared.layout-admin')
@section('title', 'Al Hamood')

@section('content')
 <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                       <!--  <h4 class="text-themecolor">Dashboard 1</h4> -->
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                           <!--  <button type="button" class="btn btn-success d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button> -->
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="card-group">
                    <!-- card -->
                    <div class="card o-income">
                        <div class="card-body">
                            <div class="d-flex m-b-30 no-block">
                                <h4 class="card-title m-b-0 align-self-center">Daily Income</h4>
                                <div class="ml-auto">
                                    <select class="custom-select border-0">
                                        <option selected="">Today</option>
                                        <option value="1">Tomorrow</option>
                                    </select>
                                </div>
                            </div>
                            <div id="income" style="height:260px; width:100%;"></div>
                            <ul class="list-inline m-t-30 text-center font-12">
                                <li><i class="fa fa-circle text-success"></i> Growth</li>
                                <li><i class="fa fa-circle text-info"></i> Net</li>
                            </ul>
                        </div>
                    </div>
                    <!-- card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex m-b-30 no-block">
                                <h4 class="card-title m-b-0 align-self-center">Visitors</h4>
                                <div class="ml-auto">
                                    <select class="custom-select border-0">
                                        <option selected="">Today</option>
                                        <option value="1">Tomorrow</option>
                                    </select>
                                </div>
                            </div>
                            <div id="visitor" style="height:260px; width:100%;"></div>
                            <ul class="list-inline m-t-30 text-center font-12">
                                <li><i class="fa fa-circle text-primary"></i> Tablet</li>
                                <li><i class="fa fa-circle text-danger"></i> Desktops</li>
                                <li><i class="fa fa-circle text-info"></i> Mobile</li>
                            </ul>
                        </div>
                    </div>
                    <!-- card -->
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">User</h4>
                        </div>
                        <div class="d-flex no-block p-15 align-items-center">
                            <div class="round round-info"><i class="icon-wallet font-16"></i></div>
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">2</h3>
                                <h6 class="text-muted font-light m-b-0">visiters</h6> </div>
                        </div>
                        <hr>
                        <div class="d-flex no-block p-15 align-items-center">
                            <div class="round round-primary"><i class="icon-umbrella font-16"></i></div>
                            <div class="m-l-10">
                                <h3 class="m-b-0">4</h3>
                                <h6 class="text-muted font-light m-b-0">users</h6></div>
                        </div>
                        <hr>
                        <div class="d-flex no-block p-15 m-b-15 align-items-center">
                            <div class="round round-danger"><i class="icon-chart font-16"></i></div>
                            <div class="m-l-10">
                                <h3 class="m-b-0">90</h3>
                                <h6 class="text-muted font-light m-b-0">admins</h6></div>
                        </div>
                    </div>
                </div>

                <!-- .right-sidebar -->
              <!--   <div class="right-sidebar">
                    <div class="slimscrollright">
                        <div class="rpanel-title"> Service Panel <span><i class="ti-close right-side-toggle"></i></span> </div>
                        <div class="r-panel-body">
                            <ul id="themecolors" class="m-t-20">
                                <li><b>With Light sidebar</b></li>
                                <li><a href="javascript:void(0)" data-skin="skin-default" class="default-theme">1</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-green" class="green-theme">2</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-red" class="red-theme">3</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-blue" class="blue-theme">4</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-purple" class="purple-theme">5</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-megna" class="megna-theme">6</a></li>
                                <li class="d-block m-t-30"><b>With Dark sidebar</b></li>
                                <li><a href="javascript:void(0)" data-skin="skin-default-dark" class="default-dark-theme">7</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-green-dark" class="green-dark-theme">8</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-red-dark" class="red-dark-theme">9</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-blue-dark" class="blue-dark-theme">10</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-purple-dark" class="purple-dark-theme">11</a></li>
                                <li><a href="javascript:void(0)" data-skin="skin-megna-dark" class="megna-dark-theme working">12</a></li>
                            </ul>
                            <ul class="m-t-20 chatonline">
                                <li><b>Chat option</b></li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/1.jpg" alt="user-img" class="img-circle"> <span>Varun Dhavan <small class="text-success">online</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/2.jpg" alt="user-img" class="img-circle"> <span>Genelia Deshmukh <small class="text-warning">Away</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/3.jpg" alt="user-img" class="img-circle"> <span>Ritesh Deshmukh <small class="text-danger">Busy</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/4.jpg" alt="user-img" class="img-circle"> <span>Arijit Sinh <small class="text-muted">Offline</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/5.jpg" alt="user-img" class="img-circle"> <span>Govinda Star <small class="text-success">online</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/6.jpg" alt="user-img" class="img-circle"> <span>John Abraham<small class="text-success">online</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/7.jpg" alt="user-img" class="img-circle"> <span>Hritik Roshan<small class="text-success">online</small></span></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)"><img src="assets/images/users/8.jpg" alt="user-img" class="img-circle"> <span>Pwandeep rajan <small class="text-success">online</small></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
@endsection
