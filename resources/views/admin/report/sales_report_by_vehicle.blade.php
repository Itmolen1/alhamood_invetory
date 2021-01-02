@extends('shared.layout-admin')
@section('title', 'Balance Sheet')

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
                            <li class="breadcrumb-item active">Balance Sheet</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">From date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">To date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Vehicle</label>
                        <select class="form-control custom-select region_id" name="vehicle_id" id="vehicle_id">
                            <option value=""><-- Select Vehicle --></option>
                            @foreach($vehicles as $vehicle)
                                @if(!empty($vehicle->registrationNumber))
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->registrationNumber }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Get Sales Report</button></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function get_pdf()
        {
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var vehicle_id = $('#vehicle_id').val();
            $.ajax({
                url: "{{ URL('PrintSalesReportByVehicle') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,vehicle_id:vehicle_id},
                success: function (result) {
                    window.open(result.url,'_blank');
                },
                error: function (errormessage) {
                    alert('No Data Found');
                }
            });
        }
    </script>
@endsection
