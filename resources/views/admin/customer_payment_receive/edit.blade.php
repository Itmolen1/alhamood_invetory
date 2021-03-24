@extends('shared.layout-admin')
@section('title', 'Edit Customer Payment')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Customer Payment</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Edit Payment</li>
                        </ol>
                        <a href="" title=""><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        {{--                        <div class="card-header bg-info">--}}
                        {{--                            <h4 class="m-b-0 text-white">Invoice</h4>--}}
                        {{--                        </div>--}}
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <input type="hidden" name="id" id="id" value="{{ $payment_receive->id }}">
                                    <?php /*<div class="row">
                                        <div class="col-md-11">
                                            <div class="form-group">
                                                {{--   <label>Select Customer</label> --}}
                                                <select class="form-control custom-select select2 customer_id" name="customer_id" id="customer_id">
                                                    <option selected readonly disabled> ---- Select Customers ---- </option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $payment_receive->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1 all">
                                            <input type="checkbox" class="form-control" name="chk[]" value="0" id="selectall">
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th>Invoice</th>
                                                <th>Vehicle</th>
                                                <th>Total</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Date</th>
                                                <th width="70">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody id="sales" style="font-size: 12px">
                                            @if(!empty($payment_receive->payment_receive_details))
                                            @foreach($payment_receive->payment_receive_details as $details)
                                                <tr>
                                                <td>{{ $details->sale->sale_details[0]->PadNumber ?? '' }}</td>
                                                <td>{{ $details->sale->sale_details[0]->vehicle->registrationNumber ?? '' }}</td>
                                                <td>{{ $details->sale->grandTotal ?? '' }}</td>
                                                <td>{{ $details->sale->paidBalance ?? '' }}</td>
                                                <td>{{ $details->sale->remainingBalance ?? '' }}</td>
                                                <td>{{ $details->sale->sale_details[0]->createdDate ?? '' }}</td>
                                                <td><input type="checkbox" class="singlechkbox" name="username" value="{{ $details->sale->paidBalance ?? 0 }}" checked /></td>
                                                </tr>
                                            @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" align="center" style="font-size: 16px !important;"> Please select customer for sale records</td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div> */ ?>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type</label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option value="bank" {{ ($payment_receive->payment_type == "bank") ? "selected":"" }}>Bank</option>
                                                    <option id="cash" value="cash" {{ ($payment_receive->payment_type == "cash") ? "selected":"" }}>Cash</option>
                                                    <option value="cheque" {{ ($payment_receive->cash == "cheque") ? "selected":"" }}>Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option selected readonly="" disabled>--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" {{ ($bank->id == $payment_receive->bank_id ?? 0) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" value="{{ $payment_receive->accountNumber }}" class="form-control accountNumber" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Transfer Date</label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ $payment_receive->transferDate }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" id="receiptNumber" name="receiptNumber" value="{{ $payment_receive->receiptNumber }}" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="" id="referenceNumber" value="{{ $payment_receive->referenceNumber }}" placeholder="Reference Number">
                                            <input type="hidden" value="{{ $payment_receive->id }}" name="Id" class="Id" id="Id">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control" name="paymentReceiveDate" value="{{ $payment_receive->paymentReceiveDate }}" id="paymentReceiveDate"  placeholder="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea style="width: 100%" id="Description" name="Description" placeholder="Description">{{ $payment_receive->Description }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $payment_receive->totalAmount }}" name="" id="" placeholder="Total Amount" disabled>
                                                <input type="hidden" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $payment_receive->totalAmount }}" name="" id="price" placeholder="Total Amount">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $payment_receive->paidAmount }}" onkeyup="toWords($('.amount').val())" name="" id="paidAmount" placeholder="Paid Amount">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <input type="text" id="SumOf" name="amountInWords" value="{{ $payment_receive->amountInWords }}" class="form-control SumOf" placeholder="Amount In words">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" id="receiver" name="receiverName" value="{{ $payment_receive->receiverName }}" class="form-control" placeholder="Enter Receiver Name">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            var val = $('#paymentType').val();
            if (val !== 'cash'){
                $('.bankTransfer').show();
            }
            else {
                $('.bankTransfer').hide();
            }
        });

        $(document).ready(function () {
            var Id=$('#bank_id').val();
            if(Id!=null)
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

        // jQuery(function($)
        // {
        //     $('body').on('click', '#selectall', function() {
        //         $('.singlechkbox').prop('checked', this.checked);
        //
        //         var totalPrice   = 0,
        //             values       = [];
        //         $('input[type=checkbox]').each( function() {
        //             if( $(this).is(':checked') ) {
        //                 values.push($(this).val());
        //                 totalPrice += parseInt($(this).val());
        //             }
        //         });
        //         $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
        //     });
        //
        //     $('body').on('click', '.singlechkbox', function() {
        //         if($('.singlechkbox').length == $('.singlechkbox:checked').length) {
        //             $('#selectall').prop('checked', true);
        //             var totalPrice   = 0,
        //                 values       = [];
        //             $('input[type=checkbox]').each( function() {
        //                 if( $(this).is(':checked') ) {
        //                     values.push($(this).val());
        //                     totalPrice += parseInt($(this).val());
        //                 }
        //             });
        //             $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
        //
        //         } else {
        //             $("#selectall").prop('checked', false);
        //             var totalPrice   = 0,
        //                 values       = [];
        //             $('input[type=checkbox]').each( function() {
        //                 if( $(this).is(':checked') ) {
        //                     values.push($(this).val());
        //                     totalPrice += parseInt($(this).val());
        //                 }
        //             });
        //             $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
        //         }
        //     });
        // });
    </script>

    <script>
        $(document).ready(function (){
            $(document).on("change", '.customer_id', function () {
                // alert();
            // $('.customer_id').change(function () {
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('customerSaleDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                //console.log(result);
                                $("#sales").html('');
                                var salesDetails = '';
                                if (result.length > 0)
                                {
                                    for (var i = 0; i < result.length; i++) {
                                        salesDetails += '<tr>';
                                        salesDetails += '<td>' + result[i].sale_details[0].PadNumber + '</td>';
                                        salesDetails += '<td>' + result[i].customer.vehicles[0].registrationNumber + '</td>';
                                        salesDetails += '<td>' + result[i].grandTotal + '</td>';
                                        salesDetails += '<td>' + result[i].paidBalance + '</td>';
                                        salesDetails += '<td>' + result[i].remainingBalance + '</td>';
                                        salesDetails += '<td>' + result[i].sale_details[0].createdDate + '<input type="hidden" class="sale_id" name="sale_id" value="' + result[i].id + '"/></td>';
                                        salesDetails += '<td><input type="checkbox" class="singlechkbox" name="username" value="' + result[i].paidBalance + '"/> </td>';
                                    }
                                }
                                else {
                                    salesDetails += '<td value="0" align="center" style="font-size: 16px" colspan="7">No Data</td>';
                                    salesDetails += '</tr>';
                                }
                                $("#sales").append(salesDetails);
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
            $('#submit').click(function (event) {

                $('#submit').text('please wait...');
                $('#submit').attr('disabled',true);

                var insert = [], chekedValue = [];
                $('.singlechkbox:checked').each(function(){
                    var currentRow = $(this).closest("tr");

                    chekedValue =
                        {
                            amountPaid: currentRow.find('.singlechkbox').val(),
                            sale_id: currentRow.find('.sale_id').val(),
                        };
                    insert.push(chekedValue);
                })
                var bank_id = $('#bank_id').val();
                if (bank_id === "")
                {
                    bank_id = 0
                }
                let details = {
                    'customer_id': $('#customer_id').val(),
                    'totalAmount': $('.totalSaleAmount').val(),
                    'paidAmount': $('#paidAmount').val(),
                    'amountInWords': $('.SumOf').val(),
                    'payment_type': $('#paymentType').val(),
                    'bank_id': bank_id,
                    'accountNumber': $('#accountNumber').val(),
                    'TransferDate': $('#TransferDate').val(),
                    'receiptNumber': $('#receiptNumber').val(),
                    'receiverName': $('#receiver').val(),
                    'referenceNumber': $('#referenceNumber').val(),
                    'paymentReceiveDate': $('#paymentReceiveDate').val(),
                    'Description': $('#Description').val(),
                    orders: insert,
                };
                //if (insert.length > 0) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Id = $('#id').val();
                    var Datas = {Data: details,Id:Id};

                    $.ajax({
                        url: "{{ URL('payment_receivesUpdate') }}",
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            if (result !== "Failed") {
                                details = [];
                                alert("Data Inserted Successfully");
                                window.location.href = "{{ route('payment_receives.index') }}";
                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                // } else
                // {
                //     alert('Please Add item to list');
                //     $('#submit').text('Save');
                //     $('#submit').attr('disabled',false);
                // }
            });
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
