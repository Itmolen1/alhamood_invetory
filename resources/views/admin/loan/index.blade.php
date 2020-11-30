@extends('shared.layout-admin')
@section('title', 'Loans List')

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
                            <li class="breadcrumb-item active">Loans</li>
                        </ol>
                        <a href="{{ route('loans.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
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
                            <h4 class="card-title">Loans</h4>
                            <h6 class="card-subtitle">All Loans</h6>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Customer / vendor Name</th>
                                        <th>Payment Type</th>
                                        <th>Date</th>
                                        <th>loan</th>
                                        <th>Status</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>

                                    @foreach($loans as $loan)
                                        <tr>
                                            <td>
                                                @if(!empty($loan->customer_id))
                                                    {{ $loan->customer->Name }}
                                                    @else
                                                    {{ $loan->employee->Name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($loan->isPay == true)
                                                    Loan Payment
                                                @else
                                                    Loan Return
                                                @endif
                                            </td>
                                            <td>{{ $loan->loanDate }}</td>
                                            <td>{{ $loan->payLoan }}</td>
                                            <td>
                                                @if($loan->isActive == true)
                                                    Active
                                                @else
                                                    UnActive
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('loans.destroy',$loan->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('loans.edit', $loan->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
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


@endsection
