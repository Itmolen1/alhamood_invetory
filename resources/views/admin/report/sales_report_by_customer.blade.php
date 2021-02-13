@extends('shared.layout-admin')
@section('title', 'Sales Report by Customer')

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
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer</label>
                        <select class="form-control custom-select customer_id" name="customer_id" id="customer_id">
                            <option value="all" selected><-- All Customers --></option>
                            @foreach($customers as $customer)
                                @if(!empty($customer->Name))
                                    <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Vehicle</label>
                        <select class="form-control custom-select region_id" name="vehicle_id" id="vehicle_id">
                            <option value="all" selected><-- All Vehicle --></option>
                            @foreach($customers as $customer)
                                @if(!empty($customers->registrationNumber))
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->registrationNumber }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">VAT FILTER</label>
                        <select name="filter" class="form-control" id="filter" required>
                            <option value="all" selected>ALL</option>
                            <option value="with">With VAT</option>
                            <option value="without">Without VAT</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info "><i class="fa fa-plus-circle"></i> Get Sales Report</button></a>
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
            var customer_id = $('#customer_id').val();
            var vehicle_id = $('#vehicle_id').val();
            var filter = $("#filter option:selected").val();
            $.ajax({
                url: "{{ URL('PrintSalesReportByCustomer') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,customer_id:customer_id,vehicle_id:vehicle_id,filter:filter},
                success: function (result) {
                    window.open(result.url,'_blank');
                },
                error: function (errormessage) {
                    alert('No Data Found');
                }
            });
        }

        $(document).ready(function () {
            $('.customer_id').change(function () {
                //alert();
                //$('.quantity').val('');
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ URL('customerDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                console.log(result);
                                //console.log(result.customer_prices[0].Rate);

                                $("#vehicle_id").html('');
                                var vehicleDetails = '';
                                if (result.customers.vehicles.length > 0)
                                {
                                    vehicleDetails += '<option value="all">All</option>';
                                    for (var i = 0; i < result.customers.vehicles.length; i++) {
                                        vehicleDetails += '<option value="' + result.customers.vehicles[i].id + '">' + result.customers.vehicles[i].registrationNumber + '</option>';
                                    }
                                }
                                else {
                                    vehicleDetails += '<option value="0">No Data</option>';
                                }
                                $("#vehicle_id").append(vehicleDetails);

                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });
        });
    </script>
@endsection
