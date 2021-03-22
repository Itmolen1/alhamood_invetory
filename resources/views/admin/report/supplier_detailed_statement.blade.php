@extends('shared.layout-admin')
@section('title', 'Supplier Statement')

@section('content')

    <style>
        .slct:focus{
            background: #aed9f6;
        }
    </style>
    <style>
        .chosen-container-single .chosen-single {
            height: 38px;
            border-radius: 3px;
            border: 1px solid #CCCCCC;
        }
        .chosen-container-single .chosen-single span {
            padding-top: 5px;
        }
        .chosen-container-single .chosen-single div b {
            margin-top: 5px;
        }
        .chosen-container-active .chosen-single,
        .chosen-container-active.chosen-with-drop .chosen-single {
            border-color: #ccc;
            border-color: rgba(82, 168, 236, .8);
            outline: 0;
            outline: thin dotted \9;
            -moz-box-shadow: 0 0 8px rgba(82, 168, 236, .6);
            box-shadow: 0 0 8px rgba(82, 168, 236, .6)
        }
    </style>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Supplier Statement</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h2>Supplier Statement</h2>
                </div>
            </div>

            @if (Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! Session::get('error') !!}</li>
                        {{Session::forget('error')}}
                    </ul>
                </div>
            @endif

            <form id="report_form" method="post" action="{{ route('ViewDetailSupplierStatement') }}" enctype="multipart/form-data">
                @csrf
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
                        <label>Supplier :- *</label>
                        <select class="form-control supplier-select supplier_id chosen-select" name="supplier_id" id="supplier_id">
                            @foreach($suppliers as $supplier)
                                @if(!empty($supplier->Name))
                                    <option value="{{ $supplier->id }}">{{ $supplier->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" id="supplier_name" name="supplier_name">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <button class="btn btn-info" type="submit"><i class="fa fa-plus-circle"></i> View Supplier Statement</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Supplier Statement</button></a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script>
        $( document ).ready(function() {
            var supplier_name=$( "#supplier_id option:selected" ).text();
            $('#supplier_name').val(supplier_name);
        });
        $( "#supplier_id" ).change(function() {
            var supplier_name=$( "#supplier_id option:selected" ).text();
            $('#supplier_name').val(supplier_name);
        });
        function get_pdf()
        {
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var supplier_id = $('#supplier_id').val();
            var supplier_name=$( "#supplier_id option:selected" ).text();
            $.ajax({
                url: "{{ URL('PrintDetailSupplierStatement') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,supplier_id:supplier_id,supplier_name:supplier_name},
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
