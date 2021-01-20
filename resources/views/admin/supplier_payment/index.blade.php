@extends('shared.layout-admin')
@section('title', 'Supplier Payment List')

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
                            <li class="breadcrumb-item active">supplier payment</li>
                        </ol>
                        <a href="{{ route('supplier_payments.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
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
                            <h4 class="card-title">Supplier Payment</h4>
                            <h6 class="card-subtitle">All Payments</h6>
                            <div class="table-responsive m-t-40">
                                <table id="supplier_payments_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Ref#</th>
                                        <th>Desc.</th>
                                        <th width="100">Push Payment</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>


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

    <script>

        $(document).ready(function () {
            $('#supplier_payments_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('supplier_payments.index') }}",
                },
                columns:[
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'paidAmount',
                        name: 'paidAmount'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
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
                ]
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
