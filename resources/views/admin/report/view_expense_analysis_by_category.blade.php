@extends('shared.layout-admin')
@section('title', 'Expense Analysis By Category')

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
                        <li class="breadcrumb-item active">Expense Analysis By Category</li>
                    </ol>
                   </div>
            </div>
        </div>
        <h4>{{$title}}</h4>
        <div class="table-responsive">
            <table border="1" cellpadding="2" cellspacing="2">
                <thead>
                <tr>
                    <th>Expense Category</th>
                    <th>Expense Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($final_array as $item)
                    <tr>
                        <td>{{$item['category_name']}}</td>
                        <td>{{$item['total_expense']}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <br>
        <h4>Total of Expense : {{$sum_of_expenses}}</h4>
    </div>
</div>
@endsection
