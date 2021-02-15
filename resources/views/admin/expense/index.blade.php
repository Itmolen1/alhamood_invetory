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
                                    <tr>
                                        <th>SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th>Suppler</th>
                                        <th style="width: 150px">Reference Number</th>
                                        <th style="width: 150px">Category</th>
                                        <th>Sub Total</th>
                                        <th>VAT</th>
                                        <th>Total Amount</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>

                                    {{--<tbody>
                                    @foreach($expenses as $expense)
                                    <tr>
                                        <td>
                                                {{ $expense->expense_details[0]->expenseDate }}
                                        </td>
                                        <td>{{ $expense->referenceNumber ?? ''}}</td>
                                        <td>{{ $expense->expense_details[0]->expense_category->Name ?? '' }}</td>
                                        <td>{{ $expense->expense_details[0]->Description ?? '' }}</td>
                                        <td>{{ $expense->subTotal}}</td>
                                        <td>{{ $expense->totalVat}}</td>
                                        <td>{{ $expense->grandTotal }}</td>
                                        <td>
                                            <form action="{{ route('expenses.destroy',$expense->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <a href="{{ route('expenses.edit', $expense->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>--}}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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
    </script>
@endsection
