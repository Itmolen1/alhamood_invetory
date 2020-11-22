@extends('shared.layout-admin')
@section('title', 'Driver Edit')

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
                    <h4 class="text-themecolor">Driver  Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Driver</li>
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
                            <h4 class="m-b-0 text-white">Driver</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('drivers.update', $driver->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Driver Name</label>
                                                <input type="text" id="driverName" name="driverName" value="{{ $driver->driverName }}" class="form-control" placeholder="Enter Driver Name">
                                                @if ($errors->has('driverName'))
                                                    <span class="text-danger">{{ $errors->first('driverName') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Selection</label>
                                                <select class="form-control custom-select customer_id select2" name="customer_id" id="customer_id">
                                                    <option>--Select Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $driver->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration date</label>
                                                <input type="date" class="form-control registrationDate" value="{{ $driver->customer->registrationDate }}" name="registrationDate" id="registrationDate" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                        <!--/span-->


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Upload File </label>
                                                <div id="img">
                                                    <figure data-effect="pop"><img width="100" src="{{url('storage/'.$driver->customer->fileUpload)}}" /></figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/row-->


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="Mobile" id="Mobile" value="{{ $driver->customer->Mobile }}" class="form-control Mobile">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="Phone" id="Phone" value="{{ $driver->customer->Phone }}" class="form-control Phone">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $driver->Description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Driver</button>
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

        /////////////////////////// customer select /////////////////
        $(document).ready(function () {

            $('.customer_id').change(function () {
                // alert();
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ URL('customerDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                //console.log(result);
                                $('#registrationDate').val(result.registrationDate);
                                $('#Phone').val(result.Phone);
                                $('#Mobile').val(result.Mobile);
                                $('#img').html('<a href="{{url('storage/')}}/'+ result.fileUpload +'"><figure data-effect="pop"><img width="100" src="{{url('storage/')}}/'+ result.fileUpload +'"  /></figure></a>');

                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });

        });
        ////////////// end of customer select ////////////////
    </script>


@endsection
