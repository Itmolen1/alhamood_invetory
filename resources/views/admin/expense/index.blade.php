@extends('shared.layout-admin')
@section('title', 'Expenses')

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
                            <li class="breadcrumb-item active">expenses</li>
                        </ol>
                        <a href="{{ route('expenses.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> create new</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Expenses</h4>
                            <h6 class="card-subtitle">All Expenses</h6>
                            <div class="table-responsive m-t-40">
                                <table id="expense_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <th>SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th>Suppler</th>
                                        <th style="width: 150px">Reference Number</th>
                                        <th style="width: 150px">Category</th>
                                        <th>Sub Total</th>
                                        <th>VAT</th>
                                        <th>Total Amount</th>
                                        <th>Action</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Expense Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <script>
        function show_detail(e)
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
                    url: "{{ URL('getExpenseDetail') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        $('#exampleModal').modal('toggle');
                        $('#modal_body').html(result);
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>
    {{-- for ajax pagination records   --}}
    <script>
        $(document).ready(function () {
            $('#expense_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_expenses') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"},
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false,
                    },
                    {
                        data: 'expenseDate',
                        name: 'expenseDate',
                    },
                    {
                        data: 'supplier',
                        name: 'supplier',
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber',
                    },
                    {
                        data: 'expenseCategory',
                        name: 'expenseCategory',
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal',
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat',
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable : false,
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>
    {{-- for ajax pagination records   --}}

    {{-- for all records   --}}
    {{--<script>
        $(document).ready(function () {
            $('#expense_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('expenses.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'expenseDate',
                        name: 'expenseDate'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'expenseCategory',
                        name: 'expenseCategory'
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal'
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat'
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>--}}
    {{-- for all records   --}}
@endsection
