@extends('shared.layout-admin')
@section('title', 'Profit & Loss')

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
                            <li class="breadcrumb-item active">Profit & Loss</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h2>Profit & Loss</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Select Month</label>
                        <input type="month" id="month" name="month" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Profit & Loss Statement</button></a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function get_pdf()
        {
            var month = $('#month').val();
            $.ajax({
                url: "{{ URL('PrintProfit_loss') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",month:month},
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
