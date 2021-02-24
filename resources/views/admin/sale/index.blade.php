@extends('shared.layout-admin')
@section('title', 'sales')

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
                            <li class="breadcrumb-item active">sale</li>
                        </ol>
                        <a href="{{ route('sales.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New sale</button></a>
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
                                        <th style="width: 100px">Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>VAT</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th style="width: 40px">Action</th>
                                    </tr>
                                    </thead>
{{--                                    <tfoot>--}}
{{--                                    <tr>--}}
{{--                                        <th style="width: 100px">Date</th>--}}
{{--                                        <th style="width: 150px">Pad #</th>--}}
{{--                                        <th style="width: 150px">Customer</th>--}}
{{--                                        <th style="width: 150px">Vehicle</th>--}}
{{--                                        <th style="width: 150px">Product</th>--}}
{{--                                        <th>Quantity</th>--}}
{{--                                        <th>Unit Price</th>--}}
{{--                                        <th>VAT</th>--}}
{{--                                        <th>Total Amount</th>--}}
{{--                                        <th>Action</th>--}}
{{--                                    </tr>--}}
{{--                                    </tfoot>--}}
                                   {{--  <tbody>
                                    @foreach($sales as $sale)
                                        <tr> --}}
                                            {{--<td>--}}

                                                {{--@if( $sale->updated_at->diffForHumans()  > '3 minutes ago')--}}
                                                    {{--<p>exist</p>--}}
                                                    {{--{{ $sale->updated_at->diffForHumans() }}--}}
                                                    {{--@else--}}
                                                    {{--<p>No</p>--}}
                                                    {{--{{ $sale->updated_at->diffForHumans() }}--}}
                                                {{--@endif--}}
                                            {{--</td>--}}
                                           {{--  <td>
                                                @if(!empty($sale->sale_details[0]->createdDate))
                                                    {{ $sale->sale_details[0]->createdDate }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($sale->sale_details[0]->PadNumber))
                                                    {{ $sale->sale_details[0]->PadNumber }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($sale->customer->Name))
                                                    {{ $sale->customer->Name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($sale->sale_details[0]->vehicle->registrationNumber))
                                                    {{ $sale->sale_details[0]->vehicle->registrationNumber }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($sale->sale_details[0]->product_Name))
                                                    {{ $sale->sale_details[0]->product->Name }}
                                                @endif
                                            </td>

                                            <td>{{ $sale->sale_details[0]->Quantity ?? 'No data' }}</td>
                                            <td>{{ $sale->sale_details[0]->Price ?? 'No data' }}</td>
                                            <td>{{ $sale->totalVat }}</td>
                                            <td>{{ $sale->grandTotal }}</td>
                                            <td>{{ $sale->paidBalance }}</td>
                                            <td>
                                                <a href="{{ route('sales.edit', $sale->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody> --}}
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
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
                        data: 'Product',
                        name: 'Product'
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
                    // {
                    //     data: 'isActive',
                    //     name: 'isActive',
                    //     orderable: false
                    // },
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
