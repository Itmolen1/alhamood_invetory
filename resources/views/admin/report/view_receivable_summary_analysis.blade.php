@extends('shared.layout-admin')
@section('title', 'View Receivable Summary Analysis')

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
                            <li class="breadcrumb-item active">Receivable Summary Analysis</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="table-responsive">
                <table border="1" cellpadding="2" cellspacing="2">
                    <thead>
                    <tr>
                        <th>Customer/Date</th>
                        @foreach ($all_dates as $date)
                            <th>{{ date('d-M', strtotime($date)) }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    {{--<tbody>
                    @foreach ($data as $item)
                        <tr>
                            @if(isset($item['customer']['Name']))
                            <td>{{$item['customer']['Name']}}</td>
                            @foreach ($all_dates as $date)
                                @if($item['RecordDate']==$date)
                                    <td style="text-align: right">{{$item['BalanceAmount']}}</td>
                                @else
                                    <td>N.A.</td>
                                @endif
                            @endforeach
                            @endif
                        </tr>
                    @endforeach
                    </tbody>--}}

                    <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{$customer->Name}}</td>
                            @foreach($all_dates as $date)
                                @foreach($data as $item)
                                    @if(isset($item['customer']['Name']))
                                    @if($item['RecordDate']==$date && $customer->Name==$item['customer']['Name'])
                                        <td>{{$item['BalanceAmount']}}</td>
                                    @endif
                                    @endif
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
