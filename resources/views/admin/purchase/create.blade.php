@extends('shared.layout-admin')
@section('title', 'PURCHASE CREATE')

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
                    <h4 class="text-themecolor">purchase</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">purchase</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Create</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Supplier Name :- <span class="required">*</span></label>
                                                <select class="form-control custom-select supplier_id chosen-select" id="supplier_id" name="supplier_id" >
                                                    <option readonly="" disabled selected>--Select Supplier--</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md 6">
                                                    <div class="form-group">
                                                        <label class="control-label">Purchase date :- <span class="required">*</span></label>
                                                        <input type="date" name="PurchaseDate" id="PurchaseDate" value="{{ date('Y-m-d') }}" class="form-control PurchaseDate" placeholder="dd/mm/yyyy">
                                                    </div>
                                                </div>
                                                <div class="col-md 6">
                                                    <div class="form-group">
                                                            <label class="control-label">Due date :- <span class="required">*</span></label>
                                                            <input type="date" name="DueDate" id="DueDate" value="{{ date('Y-m-d') }}" class="form-control DueDate" placeholder="dd/mm/yyyy">
                                                        <input type="hidden" class="form-control PurchaseNumber" name="PurchaseNumber" id="PurchaseNumber" value="{{ $purchaseNo ?? 0 }}" placeholder="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="feeds p-b-20">
                                                <li>Address <span class="text-muted" id="Address">No Address</span></li>
                                                <li>Mobile <span class="text-muted" id="Mobile">No Mobile</span></li>
                                                <li>PostCode <span class="text-muted" id="Email">No PostCode</span></li>
                                                <li>TRN <span class="text-muted" id="TRN">No TRN</span></li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">LPO Number :-</label>
                                                        <input type="text" class="form-control referenceNumber" name="referenceNumber" id="referenceNumber" placeholder="LPO Number">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
{{--                                                <th style="width: 100px">Date</th>--}}
                                                <th style="width: 150px">PRODUCT <span class="required">*</span></th>
                                                <th style="width: 140px">UNIT</th>
                                                <th style="width: 130px">PAD #</th>
                                                <th style="width: 300px">Description</th>
                                                <th style="width: 150px">QUANTITY <span class="required">*</span></th>
                                                <th style="width: 150px">PRICE <span class="required">*</span></th>
                                                <th style="width: 150px">TOTAL</th>
                                                <th style="width: 130px">VAT <span class="required">*</span></th>
                                                <th style="width: 150px">SUBTOTAL</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
{{--                                                <td> <input type="date" name="createdDate" id="createdDate"  class="form-control createdDate" value="{{ date('Y-m-d') }}" placeholder=""></td>--}}
                                                <td>
                                                    <div class="form-group">
                                                        <select name="product" class="form-control product">
                                                            <option value="0">Product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}">{{ $product->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="unit" id="unit" class="form-control unit_id">
                                                            <option class="opt" value="0">Unit</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" placeholder="Pad Number" id="PadNumber" value="{{ $PadNumber ?? 0 }}" name="PadNumber" class="PadNumber form-control" onkeypress="return ((event.charCode >= 48 && event.charCode <= 57))"></td>
                                                <td><input type="text" placeholder="Description" class="description form-control"></td>
                                                <td><input type="text"  value="0.00" placeholder="Quantity" class="quantity form-control">
{{--                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Quantity" class="quantity form-control">--}}
                                                    <input type="hidden" placeholder="Single Row Vat" value="0.00" class="singleRowVat form-control">
                                                </td>
                                                <td><input type="text" value="0.00" placeholder="Price" class="price form-control"></td>
{{--                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Price" class="price form-control"></td>--}}
                                                <td><input type="text" onfocus="this.value=''"  placeholder="Total" class="total form-control" disabled>
                                                    <input type="hidden" onClick="this.select();"  placeholder="Total" class="total form-control">
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
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note" hidden></textarea>
                                                <input type="file">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled="">
                                                <input type="hidden" value="0.00" class="form-control TotalVat">
                                            </p>

                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled>
                                                <input type="hidden" value="0.00" class="form-control GTotal" >
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control cashPaid"></p>

                                            <p>Account Closing : <input type="text" value="0.00" class="form-control closing" id="closing" readonly>
                                                <input type="hidden" value="0.00" class="form-control closing">
                                            </p>

                                            <p>Remaining Balance: <input type="text" value="0.00" class="form-control balance" id="balance" disabled="disabled">
                                                <input type="hidden" value="0.00" class="form-control balance">
                                            </p>
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
        $(document).ready(function () {
            /////////////// Add Record //////////////////////
            $('#submit').click(function () {
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
                                    product_id: currentRow.find('.product').val(),
                                    unit_id: currentRow.find('.unit_id').val(),
                                    Quantity: currentRow.find('.quantity').val(),
                                    Price: currentRow.find('.price').val(),
                                    rowTotal: currentRow.find('.total').val(),
                                    Vat: currentRow.find('.VAT').val(),
                                    rowVatAmount: currentRow.find('.singleRowVat').val(),
                                    rowSubTotal: currentRow.find('.rowTotal').val(),
                                    PadNumber: currentRow.find('.PadNumber').val(),
                                    createdDate: currentRow.find('.createdDate').val(),
                                    description: currentRow.find('.description').val(),
                                };
                            insert.push(orderItem);
                         }
                         else
                         {
                             return false;
                         }

                    });
                    let details = {
                        PurchaseNumber: $('#PurchaseNumber').val(),
                        referenceNumber: $('#referenceNumber').val(),
                        PurchaseDate: $('#PurchaseDate').val(),
                        DueDate: $('#DueDate').val(),
                        Total: $('.total').val(),
                        subTotal: $('.rowTotal').val(),
                        totalVat: $('.TotalVat').val(),
                        grandTotal: $('.GTotal').val(),
                        paidBalance: $('.cashPaid').val(),
                        remainingBalance: $('#balance').val(),
                        lastClosing: $('#closing').val(),
                        supplier_id:$('#supplier_id').val(),
                        supplierNote:$('#description').val(),
                        orders: insert,
                    }

                    if (insert.length > 0) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Datas = {Data: details};
                        console.log(Datas);
                        $.ajax({
                            url: "{{ route('purchases.store') }}",
                            type: "post",
                            data: Datas,
                            success: function (result) {
                                if (result !== "Failed") {
                                    details = [];
                                    //console.log(result);
                                    alert("Data Inserted Successfully");
                                    window.location.href = "{{ route('purchases.index') }}";
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

        /////////////////////////// supplier select /////////////////
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
                                 $('#Address').text(result.supplier.Address);
                                 $('#Mobile').text(result.supplier.Mobile);
                                 $('#Email').text(result.supplier.postCode);
                                 $('#TRN').text(result.supplier.TRNNumber);
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
        ////////////// end of supplier select ////////////////

        /////////// product select //////////////
        $(document).on("change", '.product', function () {
            var currentRow = $(this).closest('tr');
            var productId = $(this).val();
            productInfoId(productId, currentRow);
            //currentRow.find('.quantity').val('');
        });

        function productInfoId(Id, currentRow) {
            if (Id > 0)
            {
                $.ajax({
                    url: "{{ URL('productsDetails') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed") {
                            //console.log(result);

                                    $("#unit").html('');
                                    var unitDetails = '';
                                    if (result.units.length > 0)
                                    {
                                        for (var i = 0; i < result.units.length; i++) {
                                            unitDetails += '<option value="' + result.units[i].id + '">' + result.units[i].Name + '</option>';
                                        }
                                    }
                                    else {
                                        unitDetails += '<option value="0">No Data</option>';
                                    }
                                    $("#unit").append(unitDetails);
                             // currentRow.find('.unit').val(result.unit.Name);
                        } else {
                            alert(result);
                        }
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
            CountTotalVat();
        }
        ////////////////////////// end of products select //////////
    </script>
    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>
@endsection
