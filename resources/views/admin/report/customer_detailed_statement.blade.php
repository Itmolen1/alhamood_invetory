@extends('shared.layout-admin')
@section('title', 'Customer Statement')

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
                            <li class="breadcrumb-item active">Customer Statement</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h2>Customer Statement</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">From date :- *</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">To date :- *</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer :- *</label>
                        <select class="form-control supplier-select customer_id" name="customer_id" id="customer_id">
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
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Customer Statement</button></a>
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
            var customer_name=$( "#customer_id option:selected" ).text();
            $.ajax({
                url: "{{ URL('PrintDetailCustomerStatement') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,customer_id:customer_id,customer_name:customer_name},
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
