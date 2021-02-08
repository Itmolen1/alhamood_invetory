@extends('shared.layout-admin')
@section('title', 'Edit Supplier Payment')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Supplier Payment</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Supplier Payment</a></li>
                            <li class="breadcrumb-item active">Edit Payment</li>
                        </ol>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('supplier_payments.update', $supplier_payment[0]->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                            <div class="form-body">
                               <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                <option value="">--Select your Payment Type--</option>
                                                <option value="bank" @if($supplier_payment[0]->payment_type == 'bank') {{  'selected' }} @endif>Bank</option>
                                                <option id="cash" value="cash" @if($supplier_payment[0]->payment_type == 'cash') {{  'selected' }} @endif>Cash</option>
                                                <option value="cheque" @if($supplier_payment[0]->payment_type == 'cheque') {{  'selected' }} @endif>Cheque</option>
                                            </select>
                                        </div>
                                    </div>
                                   <div class="col-md-2 bankTransfer">
                                       <div class="form-group">
                                           <label>Bank Name</label>
                                           <select class="form-control custom-select" id="bank_id" name="bank_id">
                                               <option value="">--Select Bank Name--</option>
                                               @foreach($banks as $bank)
                                                   <option value="{{ $bank->id }}" @if($supplier_payment[0]->bank_id == $bank->id) {{  'selected' }} @endif>{{ $bank->Name }}</option>
                                               @endforeach
                                           </select>
                                       </div>
                                   </div>

                                    <div class="col-md-2 bankTransfer">
                                        <div class="form-group">
                                            <label class="control-label">Account Number</label>
                                            <input type="text" id="accountNumber" name="accountNumber" class="form-control accountNumber" placeholder="Enter Account Number">
                                        </div>
                                    </div>

                                    <div class="col-md-2 bankTransfer">
                                        <div class="form-group">
                                            <label class="control-label">Transfer or Deposit Date</label>
                                            <input type="date" id="TransferDate" name="TransferDate" value="{{ $supplier_payment[0]->transferDate }}" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Receipt Number</label>
                                            <input type="text" id="receiptNumber" name="receiptNumber" class="form-control" placeholder="Receipt Number" value="{{ $supplier_payment[0]->receiptNumber}}">
                                            @if ($errors->has('receiptNumber'))
                                                <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="control-label">Cheque or Ref. Number ?</label>
                                        <input type="text" class="form-control" name="" id="referenceNumber" placeholder="Cheque or Ref. Number" value="{{ $supplier_payment[0]->referenceNumber}}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="control-label">Payment Receive Date</label>
                                        <input type="date" class="form-control" name="paymentReceiveDate" id="paymentReceiveDate" value="{{ $supplier_payment[0]->supplierPaymentDate }}" placeholder="">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="control-label">Description</label>
                                        <textarea style="width: 100%" id="Description" name="Description" placeholder="Description">{{ $supplier_payment[0]->Description}}</textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 mt-2 pl-5">
                                        <div class="form-group">
                                            <label class="control-label">Total Payable Amount :- </label>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <input type="text" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="" placeholder="Total Amount" disabled value="{{ $supplier_payment[0]->totalAmount}}">
                                            <input type="hidden" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="price" placeholder="Total Amount">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 mt-2 pl-5">
                                        <div class="form-group">
                                            <label class="control-label">Total Paying Amount :- </label>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" name="" id="paidAmount" placeholder="Paying Now Amount" value="{{ $supplier_payment[0]->paidAmount}}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 mt-2 pl-5">
                                        <div class="form-group">
                                            <label class="control-label">Amount In Words :- </label>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <div class="form-group">
                                                <input type="text" id="SumOf" name="amountInWords" class="form-control SumOf" placeholder="Amount In words" value="{{ $supplier_payment[0]->amountInWords}}" style="text-transform:uppercase" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 mt-2 pl-5">
                                        <div class="form-group">
                                            <label class="control-label">Paid By :- </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="text" id="receiver" name="receiverName" class="form-control" placeholder="Enter Paid By Name" value="{{ $supplier_payment[0]->receiverName}}">
                                        </div>
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

        $(document).ready(function () {
            var Id=$('#bank_id').val();
            if(Id!='')
            {
                $.ajax({
                    // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
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

        $(document).ready(function () {
            $('#bank_id').change(function () {
                var Id = 0;
                Id = $(this).val();
                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
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
