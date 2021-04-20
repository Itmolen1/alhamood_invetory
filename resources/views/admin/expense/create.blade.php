@extends('shared.layout-admin')
@section('title', 'expense create')

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
                <h4 class="text-themecolor">Expense Registration</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">expense</li>
                    </ol>
                    <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>
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
                        <form action="#" id="add_expense" name="add_expense">
                            <div class="form-body">
                                <h3 class="card-title">Registration</h3>
                                <h6 class="required">* Fields are required please don't leave blank</h6>
                                <div class="row p-t-20">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Supplier Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select supplier_id select2 chosen-select" name="supplier_id" id="supplier_id" required>
                                                <option value="">--Select Supplier--</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Employee Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select employee_id select2 chosen-select" name="employee_id" id="employee_id" required>
                                                <option value="">--Select Employee--</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}">{{ $employee->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="feeds p-b-20">
                                            <li>Address <span class="text-muted" id="Address">No Address</span></li>
                                            <li>Mobile <span class="text-muted" id="Mobile">No Mobile</span></li>
                                            <li>Email <span class="text-muted" id="Email">No Email</span></li>
                                            <li>TRN<span class="text-muted" id="TRN"></span></li>
                                        </ul>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Expense date :- <span class="required">*</span></label>
                                            <input type="date" name="expenseDate" id="expenseDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="dd/mm/yyyy">
                                        </div>
                                        <div class="row">

                                            <div class="col-md 12" hidden>
                                                <div class="form-group">
                                                    <label class="control-label">Expense Number</label>
                                                    <input type="text" class="form-control expenseNumber" name="expenseNumber" id="expenseNumber" value="{{ $expenseNo }}" placeholder="">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Reference Number :- <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="referenceNumber" name="referenceNumber" placeholder="Reference Number" required autocomplete="off">
                                                    <span class="text-danger" id="already_exist">Already Exists</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table color-bordered-table success-bordered-table" style="height: 350px;">
                                        <thead>
                                        <tr>
                                            <th style="width: 150px">Voucher Number</th>
                                            <th style="width: 150px">Category  <span class="required">*</span></th>
                                            <th style="width: 300px">Description</th>
                                            <th style="width: 200px">Sub Total <span class="required">*</span></th>
                                            <th style="width: 120px">VAT <span class="required">*</span></th>
                                            <th style="width: 200px">Total Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody id="newRow">
                                        <tr>
                                            <td><input type="text" placeholder="" value="{{ $PadNumber }}" name="padNumber" class="padNumber form-control" autocomplete="off"></td>
                                            <td>
                                                <div class="form-group">
                                                    <select name="customer" class="form-control expense_category_id chosen-select">
                                                        @foreach($expense_categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->Name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td><input type="text" placeholder="Description" name="description" class="description form-control" autocomplete="off"></td>

                                            <td><input type="text" id="sub_total" value="0.00" placeholder="subTotal" class="total form-control">
                                                <input type="hidden" placeholder="Single Row Vat" value="0.00" class="singleRowVat form-control">
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <select name="VAT" class="form-control VAT">
                                                        <option value="0">0.00</option>
                                                        <option value="5">5.00</option>
                                                    </select>
                                                </div>
                                            </td>
                                           <td><input type="hidden" placeholder="Total" class="rowTotal form-control">
                                                <input type="text" placeholder="Total" class="rowTotal form-control" disabled="disabled">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <textarea name="" id="mainDescription" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note" hidden></textarea>
                                            <input type="file">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Total Vat: </label>
                                            <input type="text" value="0.00" class="form-control TotalVat" disabled="">
                                            <input type="hidden" value="0.00" class="form-control TotalVat" >
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label>Bank Name :- <span class="required">*</span></label>
                                            <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                <option selected readonly="" disabled>--Select Bank Name--</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label class="control-label">Cheque or Ref. Number ?  <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="ChequeNumber" id="ChequeNumber" placeholder="Cheque or Ref. Number" autocomplete="off">
                                        </div>

                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label> Grand Total: </label>
                                            <input type="text" value="0.00" class="form-control GTotal" disabled>
                                            <input type="hidden" value="0.00" class="form-control GTotal">
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
                                                <option value="bank">Bank</option>
                                                <option id="cash" value="cash">Cash</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </div>

                                        <div class="form-group bankTransfer">
                                            <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                            <input type="date" id="transferDate" name="transferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                        </div>

                                        <div class="form-actions">
                                            <p>&nbsp;</p>
                                            <button type="button" id="submit" class="btn btn-success"> <i class="fa fa-check" ></i> Save</button>
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

<script>
    $(document).ready(function () {
        $('#already_exist').hide();
        $('#referenceNumber').keyup(function () {
            var referenceNumber=0;
            referenceNumber = $('#referenceNumber').val();
            if (referenceNumber > 0)
            {
                var data={referenceNumber:referenceNumber};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckExpenseReferenceExist') }}",
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
            }
        });

    });
</script>

<script>

    $(document).ready(function () {
        $('.bankTransfer').hide();

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

        if (DoTrim(document.getElementById('sub_total').value).length == 0)
        {
            if(fields != 1)
            {
                document.getElementById("sub_total").focus();
            }
            fields = '1';
            $("#sub_total").addClass("error");
        }

        if(DoTrim(document.getElementById('payment_type').value).length == 0)
        {
            if(fields != 1)
            {
                document.getElementById("payment_type").focus();
            }
            fields = '1';
            $("#payment_type").addClass("error");
        }

        if(DoTrim(document.getElementById('supplier_id').value).length == 0)
        {
            if(fields != 1)
            {
                document.getElementById("supplier_id").focus();
            }
            fields = '1';
            $("#supplier_id").addClass("error");
        }

        if(DoTrim(document.getElementById('employee_id').value).length == 0)
        {
            if(fields != 1)
            {
                document.getElementById("employee_id").focus();
            }
            fields = '1';
            $("#employee_id").addClass("error");
        }

        // if ($('input[name="vat_per_session"]:checked').length == 0)
        // {
        //     if(fields != 1)
        //     {
        //         document.getElementById("vat_per_session").focus();
        //     }
        //     fields = '1';
        //     $("#vat_per_session").addClass("error");
        // }
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

    function validateVat()
    {
        var trn_text=$('#TRN').html();
        var vat=parseInt($('.VAT').val());
        if(trn_text!==null || trn_text!=='')
        {
            if(vat===0)
            {
                var result=confirm('Are you sure there is no VAT ?');
                if(result===true)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return true;
            }
        }
    }

    $(document).ready(function (){
        /////////////// Add Record //////////////////////
        $('#submit').click(function () {
            if(validateForm())
            {
                //check vat if there is trn available
                if(validateVat())
                {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled',true);
                    var supplierNew = $('.supplier_id').val();
                    if (supplierNew != null)
                    {
                        var insert = [], orderItem = [], nonArrayData = "";
                        $('#newRow tr').each(function () {
                            var currentRow = $(this).closest("tr");
                            if (validateRow(currentRow)) {
                                orderItem =
                                    {
                                        Total: currentRow.find('.total').val(),
                                        expenseDate: currentRow.find('.expenseDate').val(),
                                        expense_category_id: currentRow.find('.expense_category_id').val(),
                                        description: currentRow.find('.description').val(),
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
                        let details = {
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
                                url: "{{ route('expenses.store') }}",
                                type: "post",
                                data: Datas,
                                success: function (result) {
                                    var result=JSON.parse(result);
                                    if (result.result === false) {
                                        alert(result.message);
                                        window.location.href = "{{ route('expenses.create') }}";

                                    } else {
                                        alert(result.message);
                                        window.location.href = "{{ route('expenses.index') }}";
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
                }
            }
            else
            {
                alert('please enter required data');
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
                    url: "{{ URL('supplierDetails') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed") {
                             $('#Address').text(result.supplier[0].Address);
                             $('#Mobile').text(result.supplier[0].Mobile);
                             $('#Email').text(result.supplier[0].Email);
                             $('#TRN').text(result.supplier[0].TRNNumber);
                             $('#closing').val(result.closing);
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
<script>
    $(document).ready(function () {
        $('#already_exist').hide();
        $('#referenceNumber').keyup(function () {
            var supplier_id = 0;
            supplier_id = $('#supplier_id').val();
            var referenceNumber = $('#referenceNumber').val();
            if (supplier_id > 0)
            {
                var data={supplier_id:supplier_id,referenceNumber:referenceNumber};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckExpenseReferenceExist') }}",
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
            }
        });

    });
</script>
<script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>
@endsection
