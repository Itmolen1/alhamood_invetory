@extends('shared.layout-admin')
@section('title', 'View Expense Analysis')

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
                            <li class="breadcrumb-item active">Expense Analysis</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="table-responsive">
                <table border="1" cellpadding="2" cellspacing="2">
                    <thead>
                    <tr>
                        <th>Expense/Date</th>
                        @foreach ($all_dates as $date)
                            <th>{{ date('d-M', strtotime($date)) }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Amount</td>
                    @foreach ($all_expenses as $item)
                        <td>{{$item}}</td>
                    @endforeach
                    </tr>
                    </tbody>
                </table>
            </div>
            <br>
            <h4>Sum of Expense : {{$sum_of_expenses}}</h4>
            <br>
            <h4>Average of Expense : {{$average_of_expenses}}</h4>
        </div>
    </div>
@endsection
