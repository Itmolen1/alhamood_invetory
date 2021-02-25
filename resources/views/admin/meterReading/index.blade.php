@extends('shared.layout-admin')
@section('title', 'Meter Reading')

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
                            <li class="breadcrumb-item active">Meter Reading</li>
                        </ol>
                        <a href="{{ route('meter_readings.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Meter Reading</h4>
                            <h6 class="card-subtitle">All Meter Reading</h6>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th >Date</th>
                                        {{--<th>Total Purchases</th>--}}
                                        <th>Total Sales</th>
                                        <th>Total Pad Sale</th>
                                        <th>Difference</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($meter_readings as $reading)
                                        <tr>
                                            <td>{{ $reading->readingDate }}</td>
                                            {{--<td>{{ $reading->totalPurchases }}</td>--}}
                                            <td>{{ $reading->totalMeterSale ?? '' }}</td>
                                            <td>{{ $reading->totalPadSale ?? '' }}</td>
                                            <td>{{ $reading->saleDifference ?? '' }}</td>
                                            <td>
                                                <form action="{{ route('meter_readings.destroy',$reading->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('meter_readings.edit', $reading->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
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
    </div>
@endsection
