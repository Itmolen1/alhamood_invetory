@extends('shared.layout-admin')
@section('title', 'expense create')

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
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Expenses</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Supplier Name</label>
                                                <select class="form-control custom-select supplier_id select2" name="supplier_id" id="supplier_id">
                                                    <option>--Select Supplier--</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Employee Name</label>
                                                <select class="form-control custom-select employee_id select2" name="employee_id" id="employee_id">
                                                    <option>--Select Employee--</option>
                                                    @foreach($employees as $employee)
                                                        <option value="{{ $employee->id }}">{{ $employee->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->

                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <ul class="feeds p-b-20">
                                                <li>Address <span class="text-muted" id="Address">No Address</span></li>
                                                <li>Mobile <span class="text-muted" id="Mobile">No Mobile</span></li>
                                                <li>Email <span class="text-muted" id="Email">No Email</span></li>
                                                <li>TRN <span class="text-muted" id="TRN">No TRN</span></li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Expense date</label>
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
                                                        <label class="control-label">Reference Number</label>
                                                        <input type="text" class="form-control" id="referenceNumber" name="referenceNumner" placeholder="Reference Number">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->


                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Voucher Number</th>
                                                <th style="width: 150px">Category</th>
                                                <th style="width: 300px">Description</th>
                                                <th>Sub Total</th>
                                                <th style="width: 120px">VAT</th>
                                                <th>Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td> <input type="date" value="{{ date('Y-m-d') }}" name="expenseDetailDate" id="expenseDetailDate" class="form-control expenseDetailDate" placeholder=""></td>
                                                <td><input type="text" placeholder="Pad Number" name="padNumber" class="padNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class="form-control expense_category_id">
                                                            @foreach($expense_categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" placeholder="Description" name="description" class="description form-control"></td>

                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="subTotal" class="total form-control">
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
                                                {{--                                                <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>--}}
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <textarea name="" id="mainDescription" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                                <input type="file">
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled="">
                                                <input type="hidden" value="0.00" class="form-control TotalVat" >
                                            </p>


                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled>
                                                <input type="hidden" value="0.00" class="form-control GTotal">
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control cashPaid"></p>

                                            <p>Balance: <input type="text" value="0.00" class="form-control balance" disabled>
                                                <input type="hidden" value="0.00" class="form-control balance">
                                            </p>


                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" id="submit" class="btn btn-success"> <i class="fa fa-check" ></i> Save</button>
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

    <script>


        $(document).ready(function () {
            /////////////// Add Record //////////////////////
            $('#submit').click(function () {
                $('#submit').text('please wait...');
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
                                    Total: currentRow.find('.total').val(),
                                    expenseDate: currentRow.find('.expenseDetailDate').val(),
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
                        paidBalance: $('.cashPaid').val(),
                        remainingBalance: $('.balance').val(),
                        supplier_id:$('#supplier_id').val(),
                        supplierNote:$('#mainDescription').val(),
                        employee_id:$('#employee_id').val(),
                        orders: insert,
                    }
                    // var Datas = {Data: details}
                    // console.log(Datas);
                    if (insert.length > 0) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Datas = {Data: details};
                        console.log(Datas);
                        $.ajax({
                            url: "{{ route('expenses.store') }}",
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
                // alert();
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
