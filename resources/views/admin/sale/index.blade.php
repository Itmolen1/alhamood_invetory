@extends('shared.layout-admin')
@section('title', 'sales')

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
                            <li class="breadcrumb-item active">sale</li>
                        </ol>
                        <a href="{{ route('sales.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New sale</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Sale</h4>
                            <h6 class="card-subtitle">All Sales</h6>
                            <div class="table-responsive m-t-40">
                                <table id="sales_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th style="width: 150px">Pad #</th>
                                        <th style="width: 150px">Customer</th>
                                        <th style="width: 150px">Vehicle</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>VAT</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th style="width: 40px">Action</th>
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
    {{--<script>
        $(document).ready(function () {
            $('#sales_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sales.index') }}",
                    --}}{{--"url": "{{ url('all_sales') }}",--}}{{--
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'SaleDate',
                        name: 'SaleDate'
                    },
                    {
                        data: 'PadNumber',
                        name: 'PadNumber'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'registrationNumber',
                        name: 'registrationNumber'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'Price',
                        name: 'Price'
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
                        data: 'paidBalance',
                        name: 'paidBalance'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable : false,
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>--}}

    <script>
        $(document).ready(function () {
            $('#sales_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('sales.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'SaleDate',
                        name: 'SaleDate'
                    },
                    {
                        data: 'PadNumber',
                        name: 'PadNumber'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'registrationNumber',
                        name: 'registrationNumber'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'Price',
                        name: 'Price'
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
                        data: 'paidBalance',
                        name: 'paidBalance'
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
