@extends('shared.layout-admin')
@section('title', 'Invoice Edit')

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
                    <h4 class="text-themecolor">Invoices</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Invoice</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button>
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

                                    <input type="hidden" name="SaleNumber" id="SaleNumber" value="{{ $sale_details[0]->sale->SaleNumber }}">
                                    <input type="hidden" name="id" id="id" value="{{ $sale_details[0]->sale->id }}">
                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Pad #</th>
                                                <th style="width: 200px">Customer</th>
                                                <th style="width: 150px">Vehicle</th>
                                                <th style="width: 150px">Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th style="width: 120px">VAT</th>
                                                <th>Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($sale_details as $details)
                                                @if(!is_null($details->deleted_at))
                                                    <tr style="text-decoration: line-through; color:red">
                                                        <td> <input type="text" name="" id=""  class="form-control " value="{{ $details->createdDate }}" placeholder=""></td>
                                                        <td><input type="text" placeholder="Pad Number" value="{{ $details->PadNumber }}" id="" name="" class=" form-control"></td>
                                                        <td><input type="text" placeholder="customer" value="{{ $details->sale->customer->Name }}" id="" name="" class=" form-control"></td>
                                                        <td><input type="text" placeholder="vehicle" value="{{ $details->vehicle->registrationNumber }}" id="" name="" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Product" value="{{ $details->product->Name }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Quantity" value="{{ $details->Quantity }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Price" value="{{ $details->Price }}" class="form-control"></td>
                                                        <td><input type="text" placeholder="vat" value="{{ $details->VAT }}" class="form-control" disabled>
                                                        <td><input type="text" placeholder="Total" value="{{ $details->rowSubTotal }}" class="form-control" disabled="disabled"></td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>

                                            <tbody id="newRow">
                                            @foreach($sale_details as $details)
                                                @if(is_null($details->deleted_at))
                                                    <tr>
                                                        <td> <input type="date" name="createdDate" value="{{ $details->createdDate}}" id="createdDate" class="form-control createdDate" placeholder=""></td>
                                                        <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->PadNumber}}" placeholder="Pad Number" class="PadNumber form-control"></td>
                                                        <td>
                                                            <div class="form-group">
                                                                <select name="customer" class="form-control customer_id select2" id="customer_id">
                                                                    <option readonly="" disabled selected>--Customer--</option>
                                                                    @foreach($customers as $customer)
                                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $details->sale->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-group">
                                                                <select name="vehicle" id="vehicle" class="form-control vehicle_id select2">
                                                                    <option class="opt" value="{{ $sale_details[0]->vehicle->id}}">{{ $details->vehicle->registrationNumber }}</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-group">
                                                                <select name="Product_id" class="form-control product" id="product_id">
                                                                    <option readonly="" disabled selected>--Product--</option>
                                                                    @foreach($products as $product)
                                                                        <option value="{{ $product->id }}" {{ ($product->id == $details->product_id) ? 'selected':'' }}>{{ $product->Name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Quantity }}" placeholder="Quantity" class="quantity form-control">
                                                            <input type="hidden" placeholder="Total" value="{{ $sale_details[0]->rowTotal }}" class="total form-control">
                                                            <input type="hidden" placeholder="Single Row Vat" value="{{ $sale_details[0]->rowVatAmount }}" class="singleRowVat form-control">
                                                            <input type="hidden" onClick="this.select();"  placeholder="detail_Id" value="{{ $details->id }}" class="detail_Id form-control">
                                                        </td>
                                                        <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" id="Rate" value="{{ $details->Price }}" placeholder="Price" class="price form-control"></td>
                                                        <td>
                                                            <input type="text" onClick="this.setSelectionRange(0, this.value.length)" id="VAT" value="{{ $details->VAT }}" placeholder="VAT" class="VAT form-control">
                                                           {{--  <div class="form-group">
                                                                <select name="VAT" class="form-control VAT">
                                                                    <option value="0" {{ ($details->VAT == 0) ? 'selected':'' }}>0.00</option>
                                                                    <option value="5" {{ ($details->VAT == 5) ? 'selected':'' }}>5.00</option>
                                                                </select>
                                                            </div> --}}
                                                        </td>
                                                        <td><input type="hidden" placeholder="Total" value="{{ $details->rowSubTotal }}" class="rowTotal form-control">
                                                            <input type="text" placeholder="Total" class="rowTotal form-control" value="{{ $details->rowSubTotal }}" disabled="disabled">
                                                        </td>
                                                        {{--                                                <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>--}}
                                                    </tr>
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $sale_details[0]->sale->Description }}</textarea>
                                                <input type="file">
                                                <button type="button" class="btn btn-success" id="showUpdateModel" > <i class="fa fa-eye"></i> Update Notes</button>
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <p>Total Vat: <input type="text" class="form-control TotalVat" value="{{ $sale_details[0]->sale->totalVat }}" disabled="">
                                                <input type="hidden" class="form-control TotalVat" value="{{ $sale_details[0]->sale->totalVat }}">
                                            </p>


                                            <p>Grand Total: <input type="text" class="form-control GTotal" value="{{ $sale_details[0]->sale->grandTotal }}" disabled>
                                                <input type="hidden" class="form-control GTotal" value="{{ $sale_details[0]->sale->grandTotal }}" >
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" class="form-control cashPaid" value="{{ $sale_details[0]->sale->paidBalance }}"></p>

                                            <p>Balance: <input type="text" class="form-control balance" id="balance" value="{{ $sale_details[0]->sale->remainingBalance }}" disabled="disabled">
                                                <input type="hidden" class="form-control balance" value="{{ $sale_details[0]->sale->remainingBalance }}">
                                            </p>


                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="showModel"> <i class="fa fa-check"></i> Update</button>
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
                    <input class="btn btn-info" id="submit"  type="button" value="Update Purchase">
                    {{--                    <button type="button" class="btn btn-info">Send message</button>--}}
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
                                    {{ $note->user->name }}
                                </td>
                                <td>{{ $note->Description }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">

                    {{--                    <button type="button" class="btn btn-info">Send message</button>--}}
                </div>
            </div>
        </div>
    </div>

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

                $('#showUpdateModel').click(function () {
                    $('#ShowUpdates').modal();
                });

                $('#showModel').click(function () {
                    $('#updateMessage').modal();
                });
                /////////////// Add Record //////////////////////
                $('#submit').click(function () {
                    $('#submit').val('please wait...');
                    $('#submit').attr('disabled',true);
                    //alert();
                    var updateNote = $('#updateDescription').val();
                    if (updateNote !== "") {

                        var customer_id = $('.customer_id').val();
                        //alert(supplierNew);
                        if (customer_id != null) {
                            var insert = [], orderItem = [], nonArrayData = "";
                            $('#newRow tr').each(function () {
                                var currentRow = $(this).closest("tr");
                                if (validateRow(currentRow)) {
                                    orderItem =
                                        {
                                            id: currentRow.find('.detail_Id').val(),
                                            product_id: currentRow.find('.product').val(),
                                            vehicle_id: currentRow.find('.vehicle_id').val(),
                                            Quantity: currentRow.find('.quantity').val(),
                                            Price: currentRow.find('.price').val(),
                                            rowTotal: currentRow.find('.total').val(),
                                            Vat: currentRow.find('.VAT').val(),
                                            rowVatAmount: currentRow.find('.singleRowVat').val(),
                                            rowSubTotal: currentRow.find('.rowTotal').val(),
                                            PadNumber: currentRow.find('.PadNumber').val(),
                                            createdDate: currentRow.find('.createdDate').val(),
                                        };
                                    insert.push(orderItem);
                                } else {
                                    return false;
                                }

                            });
                            var Id = $('#id').val();
                            let details = {
                                Id: Id,
                                SaleNumber: $('#SaleNumber').val(),
                                SaleDate: $('#createdDate').val(),
                                Total: $('.total').val(),
                                subTotal: $('.rowTotal').val(),
                                totalVat: $('.TotalVat').val(),
                                grandTotal: $('.GTotal').val(),
                                paidBalance: $('.cashPaid').val(),
                                remainingBalance: $('#balance').val(),
                                customer_id:$('#customer_id').val(),
                                Description:$('#description').val(),
                                UpdateDescription: $('#UpdateDescription').val(),
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
                                    url: "{{ URL('salesUpdate') }}/" + Id,
                                    type: "post",
                                    data: Datas,
                                    success: function (result) {
                                        if (result !== "Failed") {
                                            details = [];
                                            //console.log(result);
                                            alert("Data Inserted Successfully");
                                            window.location.href = "{{ route('sales.index') }}";
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
                            }
                        } else {
                            alert('Select Customer first')
                        }
                    }
                    else {
                        alert('Need Update Note')
                        window.location.href = "{{ route('purchases.index') }}";
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


                $('.customer_id').change(function () {
                    //$('.quantity').val('');
                    //alert();
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
                                    console.log(result);
                                     $('#Rate').val(result.customer_prices[0].Rate);
                                    $('#VAT').val(result.customer_prices[0].VAT);
                                    $("#vehicle").html('');
                                    var vehicleDetails = '';
                                    if (result.vehicles.length > 0)
                                    {
                                        for (var i = 0; i < result.vehicles.length; i++) {
                                            vehicleDetails += '<option value="' + result.vehicles[i].id + '">' + result.vehicles[i].registrationNumber + '</option>';
                                        }
                                    }
                                    else {
                                        vehicleDetails += '<option value="0">No Data</option>';
                                    }
                                    $("#vehicle").append(vehicleDetails);
                                    var rate = result.customer_prices[0].Rate;
                                    var vat = result.customer_prices[0].VAT;
                                    totalWithCustomer(vat, rate);


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
        });







    </script>

    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>


@endsection
