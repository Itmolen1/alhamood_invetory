@extends('shared.layout-admin')
@section('title', 'Inward Loans')

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
                            <li class="breadcrumb-item active">Inward Loans</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 col-sm-2"><h4 class="card-title">Inward Loans</h4></div>
                                <div class="col-md-1 col-sm-2"><a href="{{ route('inward_loans.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a></div>
                            </div>
                            <h6 class="card-subtitle">All Inward Loans</h6>
                            <div class="table-responsive m-t-40">
                                <table id="financers_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Loan Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th>referenceNumber</th>
                                        <th>Financer</th>
                                        <th>Payment Type</th>
                                        <th>Push Loan</th>
                                        <th width="100">Action</th>
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
            $('#financers_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('inward_loans.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'loanDate',
                        name: 'loanDate'
                    },
                    {
                        data: 'totalAmount',
                        name: 'totalAmount'
                    },
                    {
                        data: 'inward_PaidBalance',
                        name: 'inward_PaidBalance'
                    },
                    {
                        data: 'inward_RemainingBalance',
                        name: 'inward_RemainingBalance'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'financer',
                        name: 'financer'
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type'
                    },
                    {
                        data: 'push',
                        name: 'push',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
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
