@extends('shared.layout-admin')
@section('title', 'Invoice create')

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
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Invoices</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Invoice</li>
                        </ol>
                        <a href="{{ route('sales.index') }}" title=""><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <input type="hidden" name="SaleNumber" id="SaleNumber" value="{{ $saleNo ?? "" }}">

                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table" style="overflow: hidden;z-index: 999;height:350px;" id="scroll_table">
                                            <thead>
                                            <tr>
                                                <th style="width: 150px">Product</th>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Pad #</th>
                                                <th style="width: 200px">Customer</th>
                                                <th style="width: 150px">Vehicle</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th style="width: 120px">VAT</th>
                                                <th>Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="Product_id" class="form-control product_id slct" id="product_id">
                                                            <option readonly="" disabled selected>--Product--</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" selected>{{ $product->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="date" name="createdDate" value="{{ date('Y-m-d') }}" id="createdDate" class="form-control createdDate" placeholder=""></td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" placeholder="Pad Number" value="{{ $PadNumber ?? "" }}" class="PadNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class=" customer_id chosen-select" id="customer_id" style="z-index: 9999 !important;overflow: hidden !important;display: block;">
                                                            <option readonly="" disabled selected>--Customer--</option>
                                                            @foreach($customers as $customer)
                                                                <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="vehicle" id="vehicle" class="form-control vehicle_id slct chosen-select">
                                                            <option class="opt" value="0">Vehicle</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td hidden="">
                                                    <div class="form-group">
                                                        <select name="unit" id="unit" class="form-control unit_id">
                                                            <option class="opt" value="1">Unit</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Quantity" class="quantity form-control">
                                                    <input type="hidden" placeholder="Total" class="total form-control">
                                                    <input type="hidden" placeholder="Single Row Vat" value="0.00" class="singleRowVat form-control">
                                                </td>

                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Price" id="Rate" class="price form-control" autocomplete="off"></td>

                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="VAT" id="VAT" class="VAT form-control">
                                                    </div>
                                                </td>

                                                <td><input type="hidden" placeholder="Total" class="rowTotal form-control">
                                                    <input type="text" placeholder="Total" class="rowTotal form-control">
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group" hidden>
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                            <div class="table-responsive" style="margin-top: 20px">
                                                <table class="table color-table inverse-table">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 100px">Pad #</th>
                                                        <th style="width: 210px">Customer</th>
                                                        <th style="width: 100px">Vehicle</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Amount</th>
                                                        <th>Paid</th>
                                                        <th>Time</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($salesRecords as $records)
                                                        <tr id="rowData" style="background: #1285ff;color: white;font-size: 12px">
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->PadNumber))
                                                                     {{ $records->sale_details[0]->PadNumber }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $records->customer->Name ?? "" }}</td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->vehicle->registrationNumber))
                                                                    {{ $records->sale_details[0]->vehicle->registrationNumber }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->Quantity))
                                                                    {{ $records->sale_details[0]->Quantity }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->Price))
                                                                       {{ $records->sale_details[0]->Price }}
                                                                @endif
                                                             </td>
                                                            <td>{{ $records->grandTotal }}</td>
                                                            <td>{{ $records->paidBalance }}</td>
                                                            <td>{{ $records->updated_at->diffForHumans() }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled="">
                                                <input type="hidden" value="0.00" class="form-control TotalVat">
                                            </p>

                                            <p>Account Closing : <input type="text" value="0.00" class="form-control closing" id="closing" readonly>
                                                <input type="hidden" value="0.00" class="form-control closing">
                                            </p>

                                            <p>Remaining Balance: <input type="text" value="0.00" id="balance" class="form-control balance" disabled>
                                                <input type="hidden" value="0.00" id="balance" class="form-control balance">
                                            </p>
                                        </div>

                                        <div class="col-md-2">

                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled>
                                                <input type="hidden" value="0.00" class="form-control GTotal" >
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control cashPaid"></p>

                                            <div class="form-actions">
                                                <p>&nbsp;</p>
                                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
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
        window.onload = function () {
            document.getElementById('customer_id').focus();
        };

        $(document).ready(function () {
            $('html, body').animate({
                scrollTop: $('.page-titles').offset().top
            }, 'slow');
        });
    </script>
    <script>
        $(document).ready(function () {

            // ///////////////////// Add new Row //////////////////////
            // $(document).on("click",'.addRow', function () {
            //     alert()
            //     var currentRow = $(this).closest("tr");
            //     var vat = currentRow.find('.VAT').val();
            //     var discount = currentRow.find('.discount').val();
            //     {
            //         $('.addRow').removeAttr("value", "");
            //         $('.addRow').attr("value", "X");
            //         $('.addRow').removeClass('btn-success').addClass('btn-danger');
            //         $('.addRow').removeClass('addRow').addClass('remove');
            //
            //         var html = '';
            //         html += '<tr>';
            //         html += '<td><select name="product" class="product form-control"> <option value="2">product name</option> </select></td>';
            //         html += '<td><input type="text" placeholder="Unit" class="unit form-control"></td>';
            //         html += '<td><input onfocus="this.value=\'\'" value="0.00" type="text" placeholder="Quantity" class="quantity form-control"><input type="hidden" placeholder="Total" class="total form-control"><input type="hidden" placeholder="Total discount" class="totalD form-control"><input type="hidden" placeholder="singleItemVat" class="singleItemVat form-control"></td>';
            //         html += '<td><input onfocus="this.value=\'\'" value="0.00" type="text" placeholder="Price" class="price form-control">';
            //         html += '<td><input type="text" placeholder="VAT" class="VAT form-control"></td></td>';
            //         html += '<td><input type="hidden" placeholder="Total" class="rowTotal form-control"><input type="text" placeholder="Row Total" class="rowTotal form-control"></td>';
            //         html += '<td><input class="btn btn-success addRow" id="addRow" type="button" value="+" /></td>';
            //         html += '</tr>';
            //         $('#newRow').append(html);
            //     }
            //
            //     // CountTotalVat();
            // });
            // ///////// end of add new row //////////////
            // ////////////// Remove row ///////////////
            // $(document).on("click",'.remove', function () {
            //     var Current = $(this).closest('tr');
            //     Current.remove();
            // });
            // /////////////end remove row //////////////


            //// accept Only Numbers /////////////////////

            //////// end Accept only Number ////////////////////

            /////////////////////////// customer select /////////////////
            $(document).ready(function () {

                /////////////// Add Record //////////////////////
                $('#submit').click(function () {

                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled',true);

                    var supplierNew = $('.customer_id').val();
                    //alert(supplierNew);
                    if (supplierNew != null)
                    {
                        var insert = [], orderItem = [], nonArrayData = "";
                        $('#newRow tr').each(function () {
                            var currentRow = $(this).closest("tr");
                            if (validateRow(currentRow)) {
                                var quantity=currentRow.find('.quantity').val();
                                var price=currentRow.find('.price').val();
                                quantity=parseFloat(quantity).toFixed(2);
                                price=parseFloat(price);
                                orderItem =
                                    {
                                        product_id: currentRow.find('.product_id').val(),
                                        unit_id: currentRow.find('.unit_id').val(),
                                        vehicle_id: currentRow.find('.vehicle_id').val(),
                                        Quantity: quantity,
                                        Price: price,
                                        rowTotal: currentRow.find('.total').val(),
                                        Vat: currentRow.find('.VAT').val(),
                                        rowVatAmount: currentRow.find('.singleRowVat').val(),
                                        rowSubTotal: currentRow.find('.rowTotal').val(),
                                        PadNumber: currentRow.find('.PadNumber').val(),
                                        createdDate: currentRow.find('.createdDate').val(),
                                    };
                                insert.push(orderItem);
                            }
                            else
                            {
                                return false;
                            }

                        });
                        let details = {
                            SaleNumber: $('#SaleNumber').val(),
                            SaleDate: $('#createdDate').val(),
                            Total: $('.total').val(),
                            subTotal: $('.rowTotal').val(),
                            totalVat: $('.TotalVat').val(),
                            grandTotal: $('.GTotal').val(),
                            paidBalance: $('.cashPaid').val(),
                            remainingBalance: $('#balance').val(),
                            lastClosing: $('#closing').val(),
                            customer_id:$('#customer_id').val(),
                            customerNote:$('#description').val(),
                            orders: insert,
                        }

                        if (insert.length > 0) {
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            var Datas = {Data: details};
                            //console.log(Datas);
                            $.ajax({
                                url: "{{ route('sales.store') }}",
                                type: "post",
                                data: Datas,
                                success: function (result) {
                                    var result=JSON.parse(result);
                                    if (result.result === false) {
                                        alert(result.message);
                                        window.location.href = "{{ route('sales.create') }}";

                                    } else {
                                        window.location.href = "{{ route('sales.create') }}";
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
                    }
                    else
                    {
                        alert('Select Customer first')
                        $('#submit').text('Save');
                        $('#submit').attr('disabled',false);
                    }

                });
                //////// end of submit Records /////////////////

                //////// validate rows ////////
                function validateRow(currentRow) {

                    var isvalid = true;
                    var rate = 0, product = 0, quantity = 0, vehicle = $('.vehicle_id').val();
                    if (parseInt(vehicle) === 0 || vehicle === ""){
                        isvalid = false;
                    }

                    product = currentRow.find('.product').val();
                    quantity  = currentRow.find('.quantity').val();
                    quantity = parseFloat(quantity).toFixed(2);
                    rate = currentRow.find('.price').val();
                    rate = parseFloat(rate).toFixed(2)

                    if (parseInt(product) === 0 || product === ""){
                        //alert(product);
                        isvalid = false;

                    }
                    // if (parseFloat(quantity) == 0 || quantity == "")
                    // {
                    //     isvalid = false;
                    // }
                    if (parseFloat(rate) == 0 || rate == "")
                    {
                        isvalid = false
                    }
                    return isvalid;
                }
                ////// end of validate row ///////////////////

                $('.customer_id').change(function () {
                    var Id = 0;
                    Id = $(this).val();

                    if (Id > 0)
                    {
                        $.ajax({
                            // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            url: "{{ URL('customerDetails') }}/" + Id,
                            type: "get",
                            dataType: "json",
                            success: function (result) {
                                if (result !== "Failed") {
                                    console.log(result.customers);
                                    $('#Rate').val(result.customers[0].customer_prices[0].Rate);
                                    $('#VAT').val(result.customers[0].customer_prices[0].VAT);

                                    $("#vehicle").html('');
                                    var vehicleDetails = '';
                                    // vehicleDetails += '<option value="">' + 'Select' + '</option>';
                                    if (result.customers[0].vehicles.length > 0)
                                    {
                                        for (var i = 0; i < result.customers[0].vehicles.length; i++) {
                                            vehicleDetails += '<option value="' + result.customers[0].vehicles[i].id + '">' + result.customers[0].vehicles[i].registrationNumber + '</option>';
                                        }
                                    }
                                    else {
                                        vehicleDetails += '<option value="0">No Data</option>';
                                    }
                                    $("#vehicle").append(vehicleDetails);
                                    $("#vehicle").trigger("chosen:updated");

                                    var rate = result.customers[0].customer_prices[0].Rate;
                                    var vat = result.customers[0].customer_prices[0].VAT;
                                    rate=parseFloat(rate).toFixed(2)
                                    vat=parseFloat(vat).toFixed(2)
                                    totalWithCustomer(vat, rate);
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

              /////////// product select //////////////
        $(document).on("change", '.product_id', function () {
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
        });
    </script>
    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>
@endsection
