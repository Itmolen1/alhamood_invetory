@extends('shared.layout-admin')
@section('title', 'Customer Advance Payment Details List')

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
                            <li class="breadcrumb-item active">Customer Advance Disburse List</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Customer Payment Details</h4>
                            <h6 class="card-subtitle">All Payment Details</h6>
                            <div class="table-responsive m-t-40">
                                <table id="customer_payments_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>PAD NUMBER</th>
                                        <th>Paid Amount</th>
                                        <th>Date</th>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($customer_advance_details as $details)
                                            <tr>
                                                <td>{{ $details->customer_advance->customer->Name ?? '' }}</td>
                                                <td>{{ $details->customer_advance->customer->Name ?? '' }}</td>
                                                <td>{{ $details->amountPaid ?? '' }}</td>
                                                <td>{{ $details->createdDate ?? '' }}</td>
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
    </div>
@endsection
