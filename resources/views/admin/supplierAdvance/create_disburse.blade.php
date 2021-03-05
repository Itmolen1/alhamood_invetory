@extends('shared.layout-admin')
@section('title', 'Create Disburse')

@section('content')

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
                            <li class="breadcrumb-item active">Supplier Advance</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <h3 class="card-title">Distribute SUPPLIER Advance Payment</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <div class="row">
                                        <label class="mt-2">Supplier Name :- <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <input type="text" id="supplier_name" name="supplier_name" class="form-control" value="{{$supplierAdvance->supplier->Name}}" readonly>
                                                <input type="hidden" id="supplier_id" name="supplier_id" value="{{$supplierAdvance->supplier->id}}">
                                                <input type="hidden" id="supplier_advance_id" name="supplier_advance_id" value="{{$supplierAdvance->id}}">
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
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Total Amount :- </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control"  name="" id="" value="{{$supplierAdvance->Amount}}"  disabled>
                                            </div>
                                        </div>

                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Selected Amount :- </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="total_selected_amount" id="total_selected_amount" placeholder="Total Amount" disabled>
                                                <input type="hidden" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="price" placeholder="Total Amount">
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
            var Id = 0;
            Id = $('#supplier_id').val();
            if (Id > 0)
            {
                $.ajax({
                    url: "{{ URL('supplierSaleDetails') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed") {
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
                        purchase_id: currentRow.find('.purchase_id').val(),
                    };
                    insert.push(chekedValue);
                })

                let details = {
                    'supplier_id': $('#supplier_id').val(),
                    'supplier_advance_id': $('#supplier_advance_id').val(),
                    'totalAmount': $('#price').val(),
                    'total_selected_amount': $('#total_selected_amount').val(),
                    orders: insert,
                };
                if (insert.length > 0) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Datas = {Data: details};
                    $.ajax({
                        url: "{{ route('supplier_advances_save_disburse') }}",
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            if (result !== "Failed") {
                                details = [];
                                alert("Data Inserted Successfully");
                                window.location.href = "{{ route('supplier_advances.index') }}";
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

    <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>
@endsection
