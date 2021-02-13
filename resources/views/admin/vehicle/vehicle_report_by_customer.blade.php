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
            var customer_id = $('#customer_id').val();
            $.ajax({
                url: "{{ URL('PrintVehicleList') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",customer_id:customer_id},
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
