@extends('shared.layout-admin')
@section('title', 'Outward Loan Payment')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">OutwardLoan Payment</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">OutwardLoan Payment</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">OutwardLoan Payment</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ url('outward_loan_save_payment',$outward_loan->id) }}" enctype="multipart/form-data" id="customer_create" onsubmit="return validateForm();">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Financer :- </label>
                                               <input type="text" readonly value="{{$outward_loan->customer->Name}}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number :- <span class="required">*</span></label>
                                                <input type="text" id="referenceNumber" name="referenceNumber" class="form-control" placeholder="Reference Number" autocomplete="off">
                                                @if ($errors->has('referenceNumber'))
                                                    <span class="text-danger">{{ $errors->first('referenceNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Payment Date :- <span class="required">*</span></label>
                                                <input type="date" name="paymentDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Note</label>
                                                <input type="text" id="Description" name="Description" class="form-control" placeholder="Note" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Loan Amount <span class="required">*</span></label>
                                                <input type="text" id="totalAmount" class="form-control" readonly value="{{$outward_loan->outward_RemainingBalance}}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Paying Amount <span class="required">*</span></label>
                                                <input type="text" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" id="amountPaid" name="amountPaid" class="form-control amount" placeholder="Enter Amount" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="control-label">Amount In Words <span class="required">*</span></label>
                                                <input type="text" id="SumOf" name="amountInWords" class="form-control SumOf" placeholder="Amount In words" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option value="">--Select your Payment Type--</option>
                                                    <option value="bank">Bank</option>
                                                    <option id="cash" value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                                @if ($errors->has('paymentType'))
                                                    <span class="text-danger">{{ $errors->first('paymentType') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row bankTransfer">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Bank Name :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option value="">--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Account Number :- <span class="required">*</span></label>
                                                <input type="text" id="accountNumber" name="accountNumber" class="form-control" placeholder="Enter Account Number" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Cheque or Ref. Number ?</label>
                                                <input type="text" id="ChequeNumber" name="ChequeNumber" class="form-control" placeholder="Enter Cheque Number" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fa fa-check"></i> Save</button>
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
        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }

        $(document).on("keyup",'#amountPaid', function () {
            var grand_total = $('#totalAmount').val();
            grand_total=parseFloat(grand_total).toFixed(2);
            grand_total=roundToTwo(grand_total);

            var cash_paid = $('#amountPaid').val();
            cash_paid=parseFloat(cash_paid).toFixed(2);
            cash_paid=roundToTwo(cash_paid);

            if(cash_paid>grand_total)
            {
                $('#amountPaid').val((grand_total.toFixed(2)));
                $('#amountPaid').focus();
            }
        });



        function DoTrim(strComp) {
            ltrim = /^\s+/
            rtrim = /\s+$/
            strComp = strComp.replace(ltrim, '');
            strComp = strComp.replace(rtrim, '');
            return strComp;
        }

        function validateForm()
        {
            /*validation*/
            var fields;
            fields = "";


            if (DoTrim(document.getElementById('referenceNumber').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("referenceNumber").focus();
                }
                fields = '1';
                $("#referenceNumber").addClass("error");
            }

            if(DoTrim(document.getElementById('paymentType').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("paymentType").focus();
                }
                fields = '1';
                $("#paymentType").addClass("error");
            }

            if(DoTrim(document.getElementById('amountPaid').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("amountPaid").focus();
                }
                fields = '1';
                $("#amountPaid").addClass("error");
            }

            var payment_type=$("#paymentType option:selected").val();
            if(payment_type=='bank' || payment_type=='cheque')
            {
                if(DoTrim(document.getElementById('bank_id').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("bank_id").focus();
                    }
                    fields = '1';
                    $("#bank_id").addClass("error");
                }

                if(DoTrim(document.getElementById('ChequeNumber').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("ChequeNumber").focus();
                    }
                    fields = '1';
                    $("#ChequeNumber").addClass("error");
                }
            }

            if (fields != "")
            {
                fields = "Please fill in the following details:" + fields;
                return false;
            }
            else
            {
                return true;
            }
            /*validation*/
        }

        $(document).ready(function () {
            $('.bankTransfer').hide();
        });

        $(document).on("change", '#paymentType', function () {
            var cashDetails = $('#paymentType').val();

            if (cashDetails === 'bank'){
                $('.bankTransfer').show();
            }
            else if(cashDetails === 'cheque')
            {
                $('.bankTransfer').show();
            }
            else
            {
                $('.bankTransfer').hide();
            }
        });
    </script>
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
    </script>
    <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>
@endsection
