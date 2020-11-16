@extends('shared.layout-admin')
@section('title', 'Loan Edit')

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
                    <h4 class="text-themecolor">Loan Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Loan</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>
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
                            <h4 class="m-b-0 text-white">Loan</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Select Employee/ Customer</label>
                                                <select class="form-control custom-select employee_customer" name="employee_customer">
                                                    <option>--Select Employee / Customer Type--</option>
                                                    <option value="employee">Employee</option>
                                                    <option value="customer">Customer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group employeeField">
                                                <label>Employee</label>
                                                <select class="form-control employee_Id" name="employee_Id">
                                                    <option>--Select Employee Type--</option>
                                                    <option value="employee">emp 1</option>
                                                    <option value="customer">emp 2</option>
                                                </select>
                                            </div>

                                            <div class="form-group customerField">
                                                <label>Customer</label>
                                                <select class="form-control customer_Id" name="customer_Id">
                                                    <option>--Select Customer Type--</option>
                                                    <option value="employee">cus 2</option>
                                                    <option value="customer">cus 3</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/row-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Remaining loan</label>
                                                <input type="text" class="form-control" placeholder="Enter Remaining Loan">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Payment Type</label>
                                                {{--                                                <p class="c2">cash</p>--}}
                                                {{--                                                <p class="c1">credit</p>--}}
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input cash">
                                                    <label class="custom-control-label" for="customRadio1">Loan Payment</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input">
                                                    <label class="custom-control-label" for="customRadio2">Loan Return</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->


                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Pay Loan</label>
                                                <input type="text" name="TRNNumber" class="form-control" placeholder="Paiy Loan">
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Date</label>
                                                <input type="date" name="TRNNumber" class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Loan</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row -->


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

           // $('.employeeField').hide();
            $('.customerField').hide();
        });

        $(document).on("change", '.employee_customer', function () {
            var val = $('.employee_customer').val();

            if (val === 'employee'){
                $('.employeeField').show();
                $('.customerField').hide();
            }
            else if(val === 'customer')
            {
                $('.employeeField').hide();
                $('.customerField').show();
            }
            else
            {

                $('.employeeField').hide();
                $('.customerField').hide();
            }
        });

    </script>


@endsection
