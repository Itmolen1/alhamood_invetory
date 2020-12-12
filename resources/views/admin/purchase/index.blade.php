@extends('shared.layout-admin')
@section('title', 'Expenses')

@section('content')

    <script type="text/javascript">
        function get_pdf(id)
        {
            $.ajax({
                type : "GET",
                url : "{{ URL('purchasePrint') }}/" + id,
            }).done(function(data){
                window.open(data,'_blank');
            });
        }
    </script>

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
                            <li class="breadcrumb-item active">Purchase</li>
                        </ol>
                        <a href="{{ route('purchases.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Purchase</button></a>
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
                            <h4 class="card-title">Purchases</h4>
                            <h6 class="card-subtitle">All Purchases</h6>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">Supplier Name</th>
                                        <th style="width: 100px">Product</th>
                                        <th style="width: 100px">Pad Number</th>
                                        <th style="width: 150px">Due Date</th>
                                        <th style="width: 150px">Amount</th>
                                        <th>Vat</th>
                                        <th>Total Amount</th>
                                        <th style="width: 100px">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($purchases as $purchase)
                                        <tr>
                                            <td>
                                                @if(!empty($purchase->supplier->Name))
                                                    {{ $purchase->supplier->Name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($purchase->purchase_details[0]->product->Name))
                                                {{ $purchase->purchase_details[0]->product->Name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($purchase->purchase_details[0]->PadNumber))
                                                {{ $purchase->purchase_details[0]->PadNumber }}
                                                @endif
                                            </td>
                                            <td>{{ $purchase->DueDate }}</td>
                                            <td>{{ $purchase->Total }}</td>
                                            <td>{{ $purchase->totalVat }}</td>
                                            <td>{{ $purchase->grandTotal }}</td>
                                            <td>
                                                <form action="{{ route('purchases.destroy',$purchase->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('purchases.edit', $purchase->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                    <a href="javascript:void(0)"  onclick="return get_pdf({{$purchase->id}})"  class=" btn btn-secondary btn-sm"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></a>
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
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->


@endsection
