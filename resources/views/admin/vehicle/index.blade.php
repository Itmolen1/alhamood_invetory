@extends('shared.layout-admin')
@section('title', 'Vehicles')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                     <h4 class="text-themecolor">Vehicles</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">vehicle</li>
                        </ol>
                        <a href="{{ route('vehicles.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New vehicle</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive ">
                                <table id="vehicle_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer Name</th>
                                        <th>Registration Number</th>
                                        <th>Description</th>
                                        <th width="100">Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        function change_status(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('ChangeVehicleStatus') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        location.reload();
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>

    <script>
        $(document).on('click', '.delete', function(){
            $('#confirmModal').modal('show');
        });
        $('#ok_button').click(function(){

            $('#ok_button').text('Deleting...');
            window.location.reload();
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#vehicle_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('vehicles.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'customerName',
                        name: 'customerName'
                    },
                    {
                        data: 'registrationNumber',
                        name: 'registrationNumber'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'isActive',
                        name: 'isActive',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });
        });
    </script>

@endsection
