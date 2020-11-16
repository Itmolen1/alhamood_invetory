@extends('shared.layout-admin')
@section('title', 'expense Edit')

@section('content')


    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
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
                    <h4 class="text-themecolor">Expense Edit</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">expense</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <!-- Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Expenses</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Vender Name</label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option>--Select Vender--</option>
                                                    <option>Transportation</option>
                                                    <option>Construction</option>
                                                    <option>Fuel traders</option>
                                                    <option>Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Employee Name</label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option>--Select Employee--</option>
                                                    <option>Transportation</option>
                                                    <option>Construction</option>
                                                    <option>Fuel traders</option>
                                                    <option>Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->

                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <ul class="feeds p-b-20">
                                                <li>Address <span class="text-muted">No Address</span></li>
                                                <li>Mobile <span class="text-muted">No Mobile</span></li>
                                                <li>Email <span class="text-muted">No Email</span></li>
                                                <li>TRN <span class="text-muted">No TRN</span></li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Expense date</label>
                                                <input type="date" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Reference Number</label>
                                                        <input type="text" class="form-control" placeholder="Reference Number">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->


                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Voucher Number</th>
                                                <th style="width: 150px">Category</th>
                                                <th style="width: 300px">Description</th>
                                                <th>Sub Total</th>
                                                <th>VAT</th>
                                                <th>Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td> <input type="date" name="saleDate" id="saleDate" class="form-control" placeholder=""></td>
                                                <td><input type="text" placeholder="Voucher Number" class="vouchereNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class="form-control customer">
                                                            <option value="0">category</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" placeholder="Description" class="discription form-control"></td>

                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="subTotal" class="total form-control"></td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="VAT" name="VAT" value="0" class="VAT form-control"></td>
                                                <td><input type="hidden" placeholder="Total" class="rowTotal form-control">
                                                    <input type="text" placeholder="Total" class="rowTotal form-control" disabled="disabled">
                                                </td>
                                                {{--                                                <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>--}}
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                                <input type="file">
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled=""></p>


                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled></p>

                                            <p>Cash Paid: <input type="text" value="0.00" class="form-control cashPaid"></p>

                                            <p>Balance: <input type="text" value="0.00" class="form-control balance"></p>


                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Expenses</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row -->

            <!-- ============================================================== -->
            <!-- End PAge Content -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Right sidebar -->
            <!-- ============================================================== -->
            <!-- .right-sidebar -->
            <div class="right-sidebar">
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
                                <a href="javascript:void(0)"><img src="../assets/images/users/1.jpg" alt="user-img" class="img-circle"> <span>Varun Dhavan <small class="text-success">online</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/2.jpg" alt="user-img" class="img-circle"> <span>Genelia Deshmukh <small class="text-warning">Away</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/3.jpg" alt="user-img" class="img-circle"> <span>Ritesh Deshmukh <small class="text-danger">Busy</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/4.jpg" alt="user-img" class="img-circle"> <span>Arijit Sinh <small class="text-muted">Offline</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/5.jpg" alt="user-img" class="img-circle"> <span>Govinda Star <small class="text-success">online</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/6.jpg" alt="user-img" class="img-circle"> <span>John Abraham<small class="text-success">online</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/7.jpg" alt="user-img" class="img-circle"> <span>Hritik Roshan<small class="text-success">online</small></span></a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"><img src="../assets/images/users/8.jpg" alt="user-img" class="img-circle"> <span>Pwandeep rajan <small class="text-success">online</small></span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
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
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->

    <script>
        $(document).ready(function () {
            // $('#paymentTermAll').hide();
            //
            // $("#customRadio1 input:radio").click(function() {
            //
            //     alert("clicked");
            //
            // });

            //
            // $('.c1').click(function () {
            //     $('#paymentTermAll').show();
            // });
            // $('.c2').click(function () {
            //     $('#paymentTermAll').hide();
            // });
        });
    </script>

    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>


@endsection
