@extends('shared.layout-admin')
@section('title', 'Edit Withdrawal')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Withdrawal</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Edit Withdrawal</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('withdrawals.update', $withdrawal->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option value="">--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" {{ ($bank->id == $withdrawal->bank_id ?? 0) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" value="" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Amount :- <span class="required">*</span></label>
                                                <input type="number" id="Amount" name="Amount" value="{{ $withdrawal->Amount }}" class="form-control" placeholder="Amount" maxlength="8" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Reference Number :- <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="Reference" id="Reference" value="{{ $withdrawal->Reference }}" placeholder="Reference Number">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="control-label">Withdrawal Date :- <span class="required">*</span></label>
                                            <input type="date" class="form-control" name="withdrawalDate" value="{{ $withdrawal->withdrawalDate }}" id="withdrawalDate">
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#bank_id').change(function () {
                var Id = 0;
                Id = $(this).val();
                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('getBankAccountDetail') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                $("#accountNumber").val('');
                                $("#accountNumber").val(result);
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

        $(document).ready(function () {
            var Id = 0;
            Id = $('#bank_id').val();
            if (Id > 0)
            {
                $.ajax({
                    url: "{{ URL('getBankAccountDetail') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed") {
                            $("#accountNumber").val('');
                            $("#accountNumber").val(result);
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
    </script>
    <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>
@endsection
