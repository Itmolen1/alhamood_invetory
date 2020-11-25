@extends('shared.layout-admin')
@section('title', 'Meter Create')

@section('content')


    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper" style="margin-bottom: 20px">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    {{--<h4 class="text-themecolor">Meter Registration</h4>--}}
                </div>
                <div class="col-md-7 align-self-center text-right">
                    {{--<div class="d-flex justify-content-end align-items-center">--}}
                        {{--<ol class="breadcrumb">--}}
                            {{--<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>--}}
                            {{--<li class="breadcrumb-item active">Meter</li>--}}
                        {{--</ol>--}}
                        {{--<button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>--}}
                    {{--</div>--}}
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
                            <h4 class="m-b-0 text-white">Meter Reader</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <form action="{{ route('meter_readers.store') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-body">

                                            <div class="row p-t-20">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Meter Name</label>
                                                        <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Meter Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <!--/row-->


                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <textarea name="Description" id="description"  cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
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

                                <div class="col-md-6">
                                    <div class="form-group" hidden>
                                        <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                    </div>
                                    <div class="table-responsive" style="margin-top: 20px">
                                        <table class="table color-table inverse-table">
                                            <thead>
                                            <tr>
                                                <th>Meter Name</th>
                                                <th>Description</th>
                                                <th style="width: 150px">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($meter_readers as $records)
                                                <tr id="rowData" style="background: #1285ff;color: white;font-size: 12px">
                                                    <td>{{ $records->Name }}</td>
                                                    <td>{{ $records->shortDescriptionForm }}</td>
                                                    <td>
                                                        <form action="{{ route('meter_readers.destroy',$records->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <a href="{{ route('meter_readers.edit', $records->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                            <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


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
    </script>


@endsection
