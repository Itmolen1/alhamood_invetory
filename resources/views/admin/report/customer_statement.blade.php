@extends('shared.layout-admin')
@section('title', 'Customer Receivable Summary')

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
                <h2 class="card-title">Customer Statement</h2>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                            <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Get Customer Statement</button></a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function get_pdf()
        {
            $.ajax({
                url: "{{ URL('PrintCustomerStatement') }}",
                type: "get",
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
