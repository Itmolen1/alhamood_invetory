@extends('shared.layout-admin')
@section('title', 'Supplier advances List')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">supplier</li>
                        </ol>
                        <a href="{{ route('supplier_advances.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Supplier Advance</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Supplier Advances</h4>
                            <h6 class="card-subtitle">All Suppliers</h6>
                            <h5 class="required">** AFTER PUSH EDIT IS NOT ALLOWED SO VERIFY ENTRY BEFORE PUSH **</h5>
                            <div class="table-responsive m-t-40">
                                <table id="supplier_advances_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Supplier Name</th>
                                        <th>Amount</th>
                                        <th>Disbursed</th>
                                        <th>Remaining</th>
                                        <th>Payment Type</th>
                                        <th>Register Date</th>
                                        <th>Transfer Date</th>
                                        <th>Push Advance</th>
                                        <th>Disburse</th>
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

        $(document).ready(function () {
            $('#supplier_advances_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('supplier_advances.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'Amount',
                        name: 'Amount'
                    },
                    {
                        data: 'spentBalance',
                        name: 'spentBalance'
                    },
                    {
                        data: 'remainingBalance',
                        name: 'remainingBalance'
                    },
                    {
                        data: 'paymentType',
                        name: 'paymentType'
                    },
                    {
                        data: 'registerDate',
                        name: 'registerDate'
                    },
                    {
                        data: 'TransferDate',
                        name: 'TransferDate'
                    },
                    {
                        data: 'push',
                        name: 'push',
                        orderable: false
                    },
                    {
                        data: 'disburse',
                        name: 'disburse',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>
    <script>
        function ConfirmDelete()
        {
            var result = confirm("Are you sure you want to delete?");
            if (result) {
                document.getElementById("deleteData").submit();
            }
        }
    </script>
@endsection
