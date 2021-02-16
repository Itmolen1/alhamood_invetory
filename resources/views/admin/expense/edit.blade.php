@extends('shared.layout-admin')
@section('title', 'expense Edit')

@section('content')

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
                <h4 class="text-themecolor">Expense Modification</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">expense</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h4 class="m-b-0 text-white">Expenses</h4>
                    </div>
                    <div class="card-body">
                        <form action="#">
                            <div class="form-body">
                                <h3 class="card-title">Edit Expense</h3>
                                <h6 class="required">* Fields are required please don't leave blank</h6>
                                <div class="row p-t-20">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Supplier Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select supplier_id select2 chosen-select" name="supplier_id" id="supplier_id" required>
                                                <option value="">--Select Supplier--</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ ($supplier->id == $expense_details[0]->expense->supplier_id) ? 'selected':'' }}>{{ $supplier->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" id="id" class="id" value="{{ $expense_details[0]->expense->id }}">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Employee Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select employee_id select2 chosen-select" name="employee_id" id="employee_id" required>
                                                <option value="">--Select Employee--</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ ($employee->id == $expense_details[0]->expense->employee_id) ? 'selected':'' }}>{{ $employee->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="feeds p-b-20">
                                            <li>Address <span class="text-muted" id="Address">
                                                    @if(!empty($expense_details[0]->expense->supplier->Address))
                                                        {{ $expense_details[0]->expense->supplier->Address }}
                                                        @else
                                                        No Address
                                                    @endif
                                                </span></li>
                                            <li>Mobile <span class="text-muted" id="Mobile">
                                                    @if(!empty($expense_details[0]->expense->supplier->Mobile))
                                                        {{ $expense_details[0]->expense->supplier->Mobile }}
                                                    @else
                                                        No Mobile
                                                    @endif
                                                </span></li>
                                            <li>Email <span class="text-muted" id="Email">
                                                    @if(!empty($expense_details[0]->expense->supplier->Email))
                                                        {{ $expense_details[0]->expense->supplier->Email }}
                                                    @else
                                                        No Email
                                                    @endif
                                                </span></li>
                                            <li>TRN <span class="text-muted" id="TRN">
                                                     @if(!empty($expense_details[0]->expense->supplier->TRNNumber))
                                                        {{ $expense_details[0]->expense->supplier->TRNNumber }}
                                                    @else
                                                        No TRN
                                                    @endif
                                                </span></li>
                                        </ul>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Expense date :- <span class="required">*</span></label>
                                            <input type="date" name="expenseDate" id="expenseDate" class="form-control" value="{{ $expense_details[0]->expense->expenseDate ?? '' }}" placeholder="dd/mm/yyyy">
                                        </div>
                                        <div class="row">
                                            <div class="col-md 12" hidden>
                                                <div class="form-group">
                                                    <label class="control-label">Expense Number</label>
                                                    <input type="text" class="form-control expenseNumber" name="expenseNumber" value="{{ $expense_details[0]->expense->expenseNumber ?? '' }}" id="expenseNumber" placeholder="">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Reference Number :- <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="referenceNumber" value="{{ $expense_details[0]->expense->referenceNumber ?? '' }}"  name="referenceNumber" placeholder="Reference Number" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table color-bordered-table success-bordered-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 150px">Voucher Number</th>
                                            <th style="width: 150px">Category <span class="required">*</span></th>
                                            <th style="width: 300px">Description</th>
                                            <th style="width: 200px">Sub Total <span class="required">*</span></th>
                                            <th style="width: 120px">VAT <span class="required">*</span></th>
                                            <th style="width: 200px">Total Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($expense_details as $details)
                                            @if(!is_null($details->deleted_at))
                                                <tr style="text-decoration: line-through; color:red">
                                                    <td><input type="text" placeholder="Pad Number" value="{{ $details->PadNumber }}" id="" name="" class=" form-control"></td>
                                                    <td><input type="text" placeholder="expense_category" value="{{ $details->expense_category->Name ?? 0 }}" class=" form-control"></td>
                                                    <td><input type="text" placeholder="Description" value="{{ $details->Description }}" class=" form-control" autocomplete="off"></td>
                                                    <td><input type="text" placeholder="Total" value="{{ $details->Total ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" placeholder="vat" value="{{ $details->VAT ?? 0 }}" class="form-control" disabled>
                                                    <td><input type="text" placeholder="rowSubTotal" value="{{ $details->rowSubTotal ?? 0 }}" class="form-control" disabled="disabled"></td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        </tbody>

                                        <tbody id="newRow">
                                        @foreach($expense_details as $details)
                                            @if(is_null($details->deleted_at))
                                                <tr>
                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->PadNumber }}" placeholder="Pad Number" name="padNumber" class="padNumber form-control"></td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="customer" class="form-control expense_category_id">
                                                                @foreach($expense_categories as $category)
                                                                    <option value="{{ $category->id }}" {{ ($category->id == $details->expense_category_id ) ? 'selected':'' }}>{{ $category->Name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td><input type="text" placeholder="Description" value="{{ $details->Description }}" name="description" class="description form-control"></td>

                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Total }}" placeholder="subTotal" class="total form-control">
                                                        <input type="hidden" placeholder="Single Row Vat" value="{{ $details->rowVatAmount }}" class="singleRowVat form-control">
                                                        <input type="hidden" placeholder="" value="{{ $details->id }}" class="detail_id form-control">
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="VAT" class="form-control VAT">
                                                                <option value="0" {{ ($details->VAT == 0) ? 'selected':'' }}>0.00</option>
                                                                <option value="5" {{ ($details->VAT == 5) ? 'selected':'' }}>5.00</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td><input type="hidden" placeholder="Total" value="{{ $details->rowSubTotal }}" class="rowTotal form-control">
                                                        <input type="text" placeholder="Total" value="{{ $details->rowSubTotal }}" class="rowTotal form-control" disabled="disabled">
                                                    </td>
                                                </tr>
                                            @endif
                                         @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <textarea name="" id="mainDescription" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note" hidden>{{ $expense_details[0]->expense->Description ?? 0 }}</textarea>
                                            <input type="file">
                                            <button type="button" class="btn btn-success" id="showUpdateModel" > <i class="fa fa-eye"></i> View Previous Update Notes</button>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Total Vat: </label>
                                            <input type="text" class="form-control TotalVat" value="{{ $expense_details[0]->expense->totalVat ?? 0 }}" disabled="">
                                            <input type="hidden" class="form-control TotalVat" value="{{ $expense_details[0]->expense->totalVat ?? 0 }}" >
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label>Bank Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                <option selected readonly="" disabled>--Select Bank Name--</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->id }}" {{ ($bank->id == $expense_details[0]->expense->bank_id) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label class="control-label">Cheque or Ref. Number ?  <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="ChequeNumber" id="ChequeNumber" placeholder="Cheque or Ref. Number" value="{{ $expense_details[0]->expense->ChequeNumber ?? '' }}" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label> Grand Total: </label>
                                            <input type="text" value="{{ $expense_details[0]->expense->grandTotal ?? 0 }}" class="form-control GTotal" disabled>
                                            <input type="hidden" vvalue="{{ $expense_details[0]->expense->grandTotal ?? 0 }}" class="form-control GTotal">
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label class="control-label">Account Number :- <span class="required">*</span></label>
                                            <input type="text" id="accountNumber" name="accountNumber" class="form-control accountNumber" placeholder="Enter Account Number" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Payment Type :- <span class="required">*</span></label>
                                            <select class="form-control custom-select" id="payment_type" name="payment_type" required>
                                                <option value="">--Select your Payment Type--</option>
                                                <option value="bank" {{ ($expense_details[0]->expense->payment_type == 'bank') ? 'selected':'' }}>Bank</option>
                                                <option value="cash" id="cash"  {{ ($expense_details[0]->expense->payment_type == 'cash') ? 'selected':'' }}>Cash</option>
                                                <option value="cheque" {{ ($expense_details[0]->expense->payment_type == 'cheque') ? 'selected':'' }}>Cheque</option>
                                            </select>
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                            <input type="date" id="transferDate" name="transferDate" value="{{ $expense_details[0]->expense->transferDate ?? date('Y-m-d') }}" class="form-control" placeholder="">
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn-success" id="showModel" > <i class="fa fa-check"></i> Update</button>
                                            <button type="button" class="btn btn-inverse">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateMessage" tabindex="-1" role="dialog" aria-labelledby="modalForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="message-texta" class="control-label">Update Note:</label>
                        <textarea class="form-control" id="UpdateDescription" placeholder="Update Note"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input class="btn btn-info" id="submit"  type="button" value="Update Expense">
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="ShowUpdates" tabindex="-1" role="dialog" aria-labelledby="modalForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <table class="table color-bordered-table success-bordered-table">
                    <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Description</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($update_notes as $note)
                        <tr>
                            <td>
                                {{ $note->user->name ?? '' }}
                            </td>
                            <td>{{ $note->Description }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

@if($expense_details[0]->expense->payment_type == 'cash')
<script>
    $(document).ready(function () {
        $('.bankTransfer').hide();
    });
</script>
@else
<script>
    $(document).ready(function () {
        $('.bankTransfer').show();
    });
</script>
@endif

<script>
    $(document).ready(function () {
        var Id=$('#bank_id').val();
        if(Id!=null)
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
        $('#showUpdateModel').click(function () {
            $('#ShowUpdates').modal();
        });

        $('#showModel').click(function () {
            $('#updateMessage').modal();
        });

        $(document).on("change", '#payment_type', function () {
            var cashDetails = $('#payment_type').val();

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

        /////////////// Add Record //////////////////////
        $('#submit').click(function () {
            $('#submit').val('please wait...');
            $('#submit').attr('disabled',true);
            var supplierNew = $('.supplier_id').val();
            //alert(supplierNew);
            if (supplierNew != null)
            {
                var insert = [], orderItem = [], nonArrayData = "";
                $('#newRow tr').each(function () {
                    var currentRow = $(this).closest("tr");
                    if (validateRow(currentRow)) {
                        orderItem =
                            {
                                id: currentRow.find('.detail_id').val(),
                                Total: currentRow.find('.total').val(),
                                expenseDate: currentRow.find('.expenseDate').val(),
                                expense_category_id: currentRow.find('.expense_category_id').val(),
                                Description: currentRow.find('.description').val(),
                                Vat: currentRow.find('.VAT').val(),
                                rowVatAmount: currentRow.find('.singleRowVat').val(),
                                rowSubTotal: currentRow.find('.rowTotal').val(),
                                padNumber: currentRow.find('.padNumber').val(),
                            };
                        insert.push(orderItem);
                    }
                    else
                    {
                        return false;
                    }

                });
                var Id = $('#id').val();
                var cashPaid = $('.cashPaid').val();
                if ( cashPaid === "")
                {
                    cashPaid = 0
                }
                let details = {
                    Id:Id,
                    expenseNumber: $('#expenseNumber').val(),
                    referenceNumber: $('#referenceNumber').val(),
                    expenseDate: $('#expenseDate').val(),
                    Total: $('.total').val(),
                    subTotal: $('.rowTotal').val(),
                    totalVat: $('.TotalVat').val(),
                    grandTotal: $('.GTotal').val(),
                    payment_type: $('#payment_type').val(),
                    bank_id: $('#bank_id').val(),
                    accountNumber: $('#accountNumber').val(),
                    transferDate: $('#transferDate').val(),
                    ChequeNumber: $('#ChequeNumber').val(),
                    supplier_id:$('#supplier_id').val(),
                    supplierNote:$('#mainDescription').val(),
                    employee_id:$('#employee_id').val(),
                    UpdateDescription:$('#UpdateDescription').val(),
                    orders: insert,
                }
                if (insert.length > 0) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Datas = {Data: details};
                    $.ajax({
                        url: "{{ URL('expenseUpdate') }}/" + Id,
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            if (result !== "Failed") {
                                details = [];
                                //console.log(result);
                                alert("Data Inserted Successfully");
                                window.location.href = "{{ route('expenses.index') }}";
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
                }
            }
            else
            {
                alert('Select Customer first')
            }

        });
        //////// end of submit Records /////////////////
    });

    //////// validate rows ////////
    function validateRow(currentRow) {

        var isvalid = true;
        var rate = 0, product = 0, quantity = 0;
        product = currentRow.find('.product').val();
        quantity  = currentRow.find('.quantity').val();
        rate = currentRow.find('.price').val();
        if (parseInt(product) === 0 || product === ""){
            //alert(product);
            isvalid = false;

        }
        if (parseInt(quantity) == 0 || quantity == "")
        {
            isvalid = false;
        }
        if (parseInt(rate) == 0 || rate == "")
        {
            isvalid = false
        }
        return isvalid;
    }
    ////// end of validate row ///////////////////

    /////////////////////////// customer select /////////////////
    $(document).ready(function () {

        $('.supplier_id').change(function () {
            var Id = 0;
            Id = $(this).val();

            if (Id > 0)
            {
                $.ajax({
                    // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url: "{{ URL('supplierDetails') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed") {
                            console.log(result);
                            $('#Address').text(result.Address);
                            $('#Mobile').text(result.Mobile);
                            $('#Email').text(result.Email);
                            $('#TRN').text(result.TRNNumber);
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
    ////////////// end of customer select ////////////////
</script>
<script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>
@endsection
