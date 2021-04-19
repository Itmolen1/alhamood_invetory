@extends('shared.layout-admin')
@section('title', 'ADD Customer Payment')

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
                <div class="col-md-8 align-self-center">
                    <h4 class="text-themecolor">Payment</h4>
                    <h3 class="required"> * Select Entries Carefully after saving Update is not allowed.</h3>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Payment</li>
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
                                    <h3 class="card-title">ADD CUSTOMER Payment</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <div class="row">
                                        <label class="mt-2">Select Customer :- <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                {{--   <label>Select Customer</label> --}}
                                                <select class="form-control custom-select select2 customer_id chosen-select" name="customer_id" id="customer_id">
                                                    <option value=""> ---- Select Customers ---- </option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1 all">
                                            <input type="checkbox" class="form-control" name="chk[]" value="0" id="selectall"><span style="margin-left: 20px;">Select All</span>
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
                                            <tr>
                                                <td colspan="7" align="center" style="font-size: 16px !important;"> Please select customer for sale records</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType" required>
                                                    <option value="">--Select your Payment Type--</option>
                                                    <option value="bank">Bank</option>
                                                    <option id="cash" value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label>Bank Name :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option selected readonly="" disabled>--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Account Number :- <span class="required">*</span></label>
                                                <input type="text" id="accountNumber" name="accountNumber" class="form-control accountNumber" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Receipt Number</label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Cheque or Ref. Number ?</label>
                                            <input type="text" class="form-control" name="" id="referenceNumber" placeholder="Cheque or Ref. Number">
                                            <span class="text-danger" id="already_exist">Similar to this may exist please verify and proceed</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="control-label">Payment Receive Date</label>
                                            <input type="date" class="form-control" name="paymentReceiveDate" id="paymentReceiveDate" value="{{ date('Y-m-d') }}" placeholder="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="control-label">Description</label>
                                            <textarea style="width: 100%" id="Description" name="Description" placeholder="Description"></textarea>
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
                                                <input type="text" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="" placeholder="Total Amount" disabled>
                                                <input type="hidden" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="price" placeholder="Total Amount">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Total Paying Amount :- <span class="required">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="form-group">
                                                <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" name="" id="paidAmount" placeholder="Paying Amount" value="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Amount In Words  :- <span class="required">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <input type="text" id="SumOf" name="amountInWords" class="form-control SumOf" placeholder="Amount In words" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Paid By :- <span class="required">*</span> </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" id="receiver" name="receiverName" class="form-control" placeholder="Enter Receiver Name">
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
    <script>
        $(document).on("focusout",'#paidAmount',function () {
            var payable = $('#price').val();
            var paying = $('#paidAmount').val();
            payable=parseFloat(payable).toFixed(2);
            paying=parseFloat(paying).toFixed(2);
            if(paying>payable)
            {
                $('#paidAmount').val((payable));
                toWords(payable);
                delete payable;
                delete paying;
            }
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#already_exist').hide();
            $('#referenceNumber').keyup(function () {
                var referenceNumber=0;
                referenceNumber = $('#referenceNumber').val();

                var data={referenceNumber:referenceNumber};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckCustomerPaymentReferenceExist') }}",
                    type: "post",
                    data: data,
                    dataType: "json",
                    success: function (result) {
                        if (result === true)
                        {
                            $('#already_exist').show();
                        }
                        else
                        {
                            $('#already_exist').hide();
                        }
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            });

        });
    </script>
    <script type="text/javascript">
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

        jQuery(function($)
        {
            $('body').on('click', '#selectall', function() {
                $('.singlechkbox').prop('checked', this.checked);

                var totalPrice   = 0,
                    values       = [];
                $('input[type=checkbox]').each( function() {
                    if( $(this).is(':checked') ) {
                        values.push($(this).val());
                        totalPrice += parseFloat($(this).val());
                    }
                });
                $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
            });

            $('body').on('click', '.singlechkbox', function() {
                if($('.singlechkbox').length == $('.singlechkbox:checked').length) {
                    $('#selectall').prop('checked', true);
                    var totalPrice   = 0,
                        values       = [];
                    $('input[type=checkbox]').each( function() {
                        if( $(this).is(':checked') ) {
                            values.push($(this).val());
                            totalPrice += parseFloat($(this).val());
                        }
                    });
                    $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));

                } else {
                    $("#selectall").prop('checked', false);
                    var totalPrice   = 0,
                        values       = [];
                    $('input[type=checkbox]').each( function() {
                        if( $(this).is(':checked') ) {
                            values.push($(this).val());
                            totalPrice += parseFloat($(this).val());
                        }
                    });
                    $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
                }
            });
        });
    </script>
    <script>
        $(document).ready(function (){
            $('.customer_id').change(function () {
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
                                    for (var i = 0; i < result.length; i++)
                                    {
                                        var registrationNumber='';
                                        if(result[i].sale_details[0]['vehicle']===null)
                                        {
                                            registrationNumber='initial';
                                        }
                                        else
                                        {
                                            registrationNumber=result[i].sale_details[0]['vehicle'].registrationNumber;
                                        }
                                        salesDetails += '<tr>';
                                        salesDetails += '<td>' + result[i].sale_details[0].PadNumber + '</td>';
                                        salesDetails += '<td>' + registrationNumber + '</td>';
                                        salesDetails += '<td>' + result[i].grandTotal + '</td>';
                                        salesDetails += '<td>' + result[i].paidBalance + '</td>';
                                        salesDetails += '<td>' + result[i].remainingBalance + '</td>';
                                        salesDetails += '<td>' + result[i].sale_details[0].createdDate + '<input type="hidden" class="sale_id" name="sale_id" value="' + result[i].id + '"/></td>';
                                        var value = result[i].grandTotal - result[i].paidBalance;
                                        salesDetails += '<td><input type="checkbox" class="singlechkbox" name="username" value="' + value + '"/> </td>';
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

            if (DoTrim(document.getElementById('customer_id').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("customer_id").focus();
                }
                fields = '1';
                $("#customer_id").addClass("error");
            }

            if (DoTrim(document.getElementById('paymentType').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("paymentType").focus();
                }
                fields = '1';
                $("#paymentType").addClass("error");
            }

            if(DoTrim(document.getElementById('referenceNumber').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("referenceNumber").focus();
                }
                fields = '1';
                $("#referenceNumber").addClass("error");
            }

            if(DoTrim(document.getElementById('paidAmount').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("paidAmount").focus();
                }
                fields = '1';
                $("#paidAmount").addClass("error");
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
            }

            if(DoTrim(document.getElementById('receiver').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("receiver").focus();
                }
                fields = '1';
                $("#receiver").addClass("error");
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
            $('#submit').click(function (event) {
                if(validateForm()) {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled', true);

                    var insert = [], chekedValue = [];
                    $('.singlechkbox:checked').each(function () {
                        var currentRow = $(this).closest("tr");

                        // currentRow.find('.singlechkbox').val(),
                        // chekedValue .push($(this).val());
                        // alert(chekedValue);

                        chekedValue =
                            {
                                amountPaid: currentRow.find('.singlechkbox').val(),
                                sale_id: currentRow.find('.sale_id').val(),
                            };
                        insert.push(chekedValue);
                    })
                    var bank_id = $('#bank_id').val();
                    if (bank_id === "") {
                        bank_id = 0
                    }
                    let details = {
                        'customer_id': $('#customer_id').val(),
                        'totalAmount': $('#price').val(),
                        'payment_type': $('#paymentType').val(),
                        'bank_id': bank_id,
                        'accountNumber': $('#accountNumber').val(),
                        'TransferDate': $('#paymentReceiveDate').val(),
                        'amountInWords': $('#SumOf').val(),
                        'receiptNumber': $('#receiptNumber').val(),
                        'receiverName': $('#receiver').val(),
                        'referenceNumber': $('#referenceNumber').val(),
                        'paymentReceiveDate': $('#paymentReceiveDate').val(),
                        'paidAmount': $('#paidAmount').val(),
                        'Description': $('#Description').val(),
                        orders: insert,
                    };
                    if (insert.length > 0) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Datas = {Data: details};
                        console.log(Datas);
                        $.ajax({
                            url: "{{ route('payment_receives.store') }}",
                            type: "post",
                            data: Datas,
                            success: function (result) {
                                if (result !== "Failed") {
                                    details = [];
                                    console.log(result);
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
                    } else {
                        alert('Please Add item to list');
                        $('#submit').text('Save');
                        $('#submit').attr('disabled', false);
                    }
                }
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
