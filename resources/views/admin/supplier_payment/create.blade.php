@extends('shared.layout-admin')
@section('title', 'New Payment')

@section('content')


    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Payment</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Payment</li>
                        </ol>
                        <a href="" title=""><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button></a>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <!-- Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        {{--                        <div class="card-header bg-info">--}}
                        {{--                            <h4 class="m-b-0 text-white">Invoice</h4>--}}
                        {{--                        </div>--}}
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <div class="row">
                                        <div class="col-md-11">
                                            <div class="form-group">
                                                {{--   <label>Select Customer</label> --}}
                                                <select class="form-control custom-select select2 supplier_id" name="supplier_id" id="supplier_id">
                                                    <option selected readonly disabled> ---- Select suppliers ---- </option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->Name }}</option>
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
                                                <th>Total</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Date</th>
                                                <th width="70">Action</th>

                                            </tr>
                                            </thead>
                                            <tbody id="purchases" style="font-size: 12px">
                                            <tr>
                                                <td colspan="7" align="center" style="font-size: 16px !important;"> Please select customer for sale records</td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type</label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option disabled readonly="" selected>--Select your Payment Type--</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option id="cash" value="cash">Cash</option>
                                                    <option value="cheque">Check Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option selected readonly="" disabled>--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" class="form-control accountNumber" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Transfer Date</label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input type="text" id="receiptNumber" name="receiptNumber" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="" id="referenceNumber" placeholder="Reference Number">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control" name="paymentReceiveDate" id="paymentReceiveDate" value="{{ date('Y-m-d') }}" placeholder="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
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
                                                <label class="control-label">Total Paying Amount :- </label>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="form-group">
                                                <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" name="" id="paidAmount" placeholder="Paying Now Amount">
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
                                                    <input type="text" id="SumOf" name="amountInWords" class="form-control SumOf" placeholder="Amount In words">
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
                                                <input type="text" id="receiver" name="receiverName" class="form-control" placeholder="Enter Paid By Name">
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
            <!-- Row -->



        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->

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
            $('.supplier_id').change(function () {
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('supplierSaleDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                console.log(result);
                                $("#purchases").html('');
                                var Details = '';
                                if (result.length > 0)
                                {
                                    for (var i = 0; i < result.length; i++) {
                                        Details += '<tr>';
                                        Details += '<td>' + result[i].purchase_details[0].PadNumber + '</td>';
                                        Details += '<td>' + result[i].grandTotal + '</td>';
                                        Details += '<td>' + result[i].paidBalance + '</td>';
                                        Details += '<td>' + result[i].remainingBalance + '</td>';
                                        Details += '<td>' + result[i].purchase_details[0].createdDate + '<input type="hidden" class="purchase_id" name="purchase_id" value="' + result[i].id + '"/></td>';
                                        var value = result[i].grandTotal - result[i].paidBalance;
                                        Details += '<td><input type="checkbox" class="singlechkbox" name="username" value="' + parseFloat(value).toFixed(2) + '"/> </td>';

                                    }
                                }
                                else {
                                    Details += '<td value="0" align="center" style="font-size: 16px" colspan="7">No Data</td>';
                                    Details += '</tr>';
                                }
                                $("#purchases").append(Details);
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

                    // currentRow.find('.singlechkbox').val(),
                    // chekedValue .push($(this).val());
                    // alert(chekedValue);

                    chekedValue =
                        {
                            amountPaid: currentRow.find('.singlechkbox').val(),
                            purchase_id: currentRow.find('.purchase_id').val(),
                        };
                    insert.push(chekedValue);
                })
                var bank_id = $('#bank_id').val();
                if (bank_id === "")
                {
                    bank_id = 0
                }
                let details = {
                    'supplier_id': $('#supplier_id').val(),
                    'totalAmount': $('#price').val(),
                    'payment_type': $('#paymentType').val(),
                    'bank_id': bank_id,
                    'accountNumber': $('#accountNumber').val(),
                    'TransferDate': $('#TransferDate').val(),
                    'amountInWords': $('#SumOf').val(),
                    'receiptNumber': $('#receiptNumber').val(),
                    'receiverName': $('#receiver').val(),
                    'referenceNumber': $('#referenceNumber').val(),
                    'supplierPaymentDate': $('#paymentReceiveDate').val(),
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
                        url: "{{ route('supplier_payments.store') }}",
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            if (result !== "Failed") {
                                details = [];
                                console.log(result);
                                alert("Data Inserted Successfully");
                                window.location.href = "{{ route('supplier_payments.index') }}";

                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                } else
                {
                    alert('Please Add item to list');
                    $('#submit').text('Save');
                    $('#submit').attr('disabled',false);
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
