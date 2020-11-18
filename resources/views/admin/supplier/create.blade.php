@extends('shared.layout-admin')
@section('title', 'Supplier create')

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
                    <h4 class="text-themecolor">Supplier Registration</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">supplier</li>
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
                            <h4 class="m-b-0 text-white">Supplier</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('suppliers.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Company Name</label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Customer Company Name">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Owner/Representative Name</label>
                                                <input type="text" id="Representative" name="Representative" class="form-control" placeholder="Enter Owner/Representative Name">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Type</label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option>--Select your Company Type--</option>
                                                    <option value="transportation">Transportation</option>
                                                    <option value="construction">Construction</option>
                                                    <option value="fuelTraders">Fuel traders</option>
                                                    <option value="others">Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration date</label>
                                                <input type="date" name="registrationDate" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type</label>
                                                <select class="form-control custom-select paymentType" name="paymentType">
                                                    <option>--Select your Payment Type--</option>
                                                    <option value="cash">cash</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group" id="paymentTermAll">
                                                <label class="control-label">Payment Term</label>
                                                <select class="form-control custom-select" data-placeholder="Choose a Category" name="paymentTerm" id="paymentTerm" tabindex="1">
                                                    <option>--Select your Company Type--</option>
                                                    <option value="Category 2">5 days</option>
                                                    <option value="Category 3">10 days</option>
                                                    <option value="Category 4">15 days</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->

                                    </div>

                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">TRN Number</label>
                                                <input type="text" name="TRNNumber" class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Upload File</label>
                                                <input type="file" name="fileUpload" class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="Mobile" placeholder="Mobile" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="Phone" placeholder="Phone" class="form-control">
                                            </div>
                                        </div>
                                    </div>


                                    <h3 class="box-title m-t-40">Address</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <div class="form-group">
                                                <label>Street</label>
                                                <input type="text" name="Address" placeholder="Address" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" name="City" placeholder="City" class="form-control">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="State" PLACEHOLDER="State" class="form-control">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Post Code</label>
                                                <input type="text" name="postCode" placeholder="PostCode" class="form-control">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Country</label>
                                                <select class="form-control custom-select">
                                                    <option>--Select your Country--</option>
                                                    <option>UAE</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
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
            $('#paymentTermAll').hide();
        });
        $(document).on("change", '.paymentType', function () {
            var cash = $('.paymentType').val();

            if (cash === 'credit'){
                $('#paymentTermAll').show();
            }
            else
            {
                $('#paymentTermAll').hide();
            }
        });
    </script>


@endsection
