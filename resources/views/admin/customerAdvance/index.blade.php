@extends('shared.layout-admin')
@section('title', 'Customer advances List')

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
                    <!-- <h4 class="text-themecolor">diensten</h4> -->
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">customer</li>
                        </ol>
                        <a href="{{ route('customer_advances.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Customer Advance</button></a>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Customer Advances</h4>
                            <h6 class="card-subtitle">All Advances</h6>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Amount</th>
                                        <th>Payment Type</th>
                                        <th>Register Date</th>
                                        <th>Transfer Date</th>
                                        <th width="100">Push Advance</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($customerAdvances as $advance)
                                        <tr>
                                            <td>{{ $advance->customer->Name ?? 'No data' }}</td>
                                            <td>{{ $advance->Amount }}</td>
                                            <td>{{ $advance->paymentType }}</td>
                                            <td>{{ $advance->registerDate }}</td>
                                            <td>{{ $advance->TransferDate }}</td>

                                            <td>
                                                @if($advance->isPushed == false)
                                                <form action="{{ url('customer_advances_push',$advance->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to push?')"><i style="font-size: 20px" class="fa fa-arrow-circle-o-up"></i> Push</button>
                                                </form>
                                                @else
                                                    <button type="submit" class=" btn btn-default btn-sm" ><i style="font-size: 20px" class="fa fa-external-link"> Pushed</i> </button>
                                                @endif
                                            </td>
                                            <td>
                                                @if($advance->isPushed == false)
                                                <form action="{{ route('customer_advances.destroy',$advance->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('customer_advances.edit', $advance->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                    <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                </form>
                                                @else
                                                    <button type="submit" class=" btn btn-default btn-sm" ><i style="font-size: 20px" class="fa fa-ban"> No Action</i> </button>
                                                @endif
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

    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="text-align: center !important;">

                        <h2 class="modal-title" >Confirmation</h2>
                    </div>
                    <div class="modal-body">
                        <h4 align="center" style="margin:0;">Are you sure you want to remove this data?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
        </div>
    </div>

    <script>
        // var id;

        $(document).on('click', '.delete', function(){
            //  id = $(this).attr('id');
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function(){

            $('#ok_button').text('Deleting...');
            window.location.reload();
        });

    </script>



@endsection
