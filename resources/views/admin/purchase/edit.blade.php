@extends('shared.layout-admin')
@section('title', 'purchase create')

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
                            <h4 class="m-b-0 text-white">Create</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Supplier Name</label>
                                                <select class="form-control custom-select supplier_id" id="supplier_id" name="supplier_id">
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" {{ ($supplier->id == $purchase_details[0]->purchase->supplier_id) ? 'selected':'' }}>{{ $supplier->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="hidden" value="{{ $purchase_details[0]->purchase->id ?? 0 }}" name="id" id="id">
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md 6">
                                                    <div class="form-group">
                                                        <label class="control-label">purchase date</label>
                                                        <input type="date" name="PurchaseDate" id="PurchaseDate" value="{{ $purchase_details[0]->purchase->PurchaseDate ?? 0 }}" class="form-control PurchaseDate" placeholder="dd/mm/yyyy">
                                                    </div>
                                                </div>
                                                <div class="col-md 6">
                                                    <div class="form-group">
                                                            <label class="control-label">Due date</label>
                                                            <input type="date" name="DueDate" id="DueDate" value="{{ $purchase_details[0]->purchase->DueDate ?? 0 }}" class="form-control DueDate" placeholder="dd/mm/yyyy">
                                                            <input type="hidden" class="form-control PurchaseNumber" value="{{ $purchase_details[0]->purchase->PurchaseNumber ?? 0 }}"  name="PurchaseNumber" id="PurchaseNumber" value="" placeholder="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/row-->

                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <ul class="feeds p-b-20">
                                                <li>Address <span class="text-muted" id="Address">
                                                         @if(!empty($purchase_details[0]->purchase->supplier->Address))
                                                            {{ $purchase_details[0]->purchase->supplier->Address  }}
                                                        @else
                                                            No TRN
                                                        @endif
                                                    </span></li>
                                                <li>Mobile <span class="text-muted" id="Mobile">
                                                        @if(!empty($purchase_details[0]->purchase->supplier->Mobile))
                                                            {{ $purchase_details[0]->purchase->supplier->Mobile }}
                                                        @else
                                                            No TRN
                                                        @endif
                                                    </span></li>
                                                <li>PostCode <span class="text-muted" id="Email">
                                                        @if(!empty($purchase_details[0]->purchase->supplier->postCode))
                                                            {{ $purchase_details[0]->purchase->supplier->postCode }}
                                                        @else
                                                            No TRN
                                                        @endif
                                                    </span></li>
                                                <li>TRN <span class="text-muted" id="TRN">
                                                        @if(!empty($purchase_details[0]->purchase->supplier->TRNNumber))
                                                            {{ $purchase_details[0]->purchase->supplier->TRNNumber }}
                                                            @else
                                                            No TRN
                                                        @endif
                                                    </span></li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Reference Number</label>
                                                        <input type="text" class="form-control referenceNumber" value="{{ $purchase_details[0]->purchase->referenceNumber ?? 0 }}" name="referenceNumber" id="referenceNumber" placeholder="Reference Number">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">purchase #</th>
                                                <th style="width: 150px">product</th>
                                                <th style="width: 100px">Unit</th>
                                                <th style="width: 150px">Description</th>
                                                <th>quantity</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th style="width: 100px">VAT</th>
                                                <th style="width: 150px">Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($purchase_details as $details)
                                                @if(!is_null($details->deleted_at))
                                                    <tr style="text-decoration: line-through; color:red">
                                                        <td> <input type="text" name="" id=""  class="form-control " value="{{ $details->createdDate }}" placeholder=""></td>
                                                        <td><input type="text" placeholder="Pad Number" value="{{ $details->PadNumber }}" id="" name="" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Product" value="{{ $details->product->Name ?? '' }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Unit" value="{{ $details->unit->Name ?? '' }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Description" value="{{ $details->Description ?? '' }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Quantity" value="{{ $details->Quantity }}" class=" form-control"></td>
                                                        <td><input type="text" placeholder="Price" value="{{ $details->Price }}" class="form-control"></td>
                                                        <td><input type="text" placeholder="Total" value="{{ $details->rowTotal }}" class="form-control" disabled></td>
                                                        <td><input type="text" placeholder="vat" value="{{ $details->VAT }}" class="form-control" disabled>
                                                        <td><input type="text" placeholder="Total" value="{{ $details->rowSubTotal }}" class="form-control" disabled="disabled"></td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>

                                            <tbody id="newRow">
                                            @foreach($purchase_details as $details)
                                                @if(is_null($details->deleted_at))
                                            <tr>
                                                <td> <input type="date" name="createdDate" id="createdDate"  class="form-control createdDate" value="{{ $details->createdDate }}" placeholder=""></td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" placeholder="Pad Number" id="PadNumber" name="PadNumber" value="{{ $details->PadNumber }}" class="PadNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="product" class="form-control product">
                                                            <option value="0">Product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" {{ ($product->id == $details->product_id) ? 'selected':'' }}>{{ $product->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="unit" id="unit" class="form-control unit_id">
                                                            @foreach ($units as $unit)
                                                            <option class="opt" value="{{ $unit->id }}" {{ ($unit->id == $details->unit_id) ? 'selected':'' }}>{{ $unit->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" placeholder="Description" value="{{ $details->Description }}"  class="description form-control"></td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Quantity }}"  placeholder="Quantity" class="quantity form-control">
                                                    <input type="hidden" placeholder="Single Row Vat" value="{{ $details->rowVatAmount }}"  class="singleRowVat form-control">
                                                </td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Price }}" placeholder="Price" class="price form-control"></td>
                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)"  placeholder="Total" value="{{ $details->rowTotal }}" class="total form-control" disabled>
                                                    <input type="hidden" onClick="this.select();"  placeholder="Total" value="{{ $details->rowTotal }}" class="total form-control">
                                                    <input type="hidden" onClick="this.select();"  placeholder="detail_Id" value="{{ $details->id }}" class="detail_Id form-control">
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
                                                    <input type="text" placeholder="Total" class="rowTotal form-control" value="{{ $details->rowSubTotal }}" disabled="disabled">
                                                </td>
                                           </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <textarea name="" id="PurchaseDescription" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $purchase_details[0]->purchase->supplierNote ?? '' }}</textarea>
                                                <input type="file">
                                                <button type="button" class="btn btn-success" id="showUpdateModel" > <i class="fa fa-eye"></i> Update Notes</button>
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <p>Total Vat: <input type="text" class="form-control TotalVat" value="{{ $purchase_details[0]->purchase->totalVat ?? 0 }}" disabled="">
                                                <input type="hidden" class="form-control TotalVat" value="{{ $purchase_details[0]->purchase->totalVat ?? 0 }}">
                                            </p>


                                            <p>Grand Total: <input type="text" class="form-control GTotal" value="{{ $purchase_details[0]->purchase->grandTotal ?? 0 }}" disabled>
                                                <input type="hidden" class="form-control GTotal" value="{{ $purchase_details[0]->purchase->grandTotal ?? 0 }}" >
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" class="form-control cashPaid" value="{{ $purchase_details[0]->purchase->paidBalance ?? 0 }}"></p>

                                            <p>Balance: <input type="text" class="form-control balance" id="balance" value="{{ $purchase_details[0]->purchase->remainingBalance ?? 0 }}" disabled="disabled">
                                                <input type="hidden" class="form-control balance" value="{{ $purchase_details[0]->purchase->remainingBalance ?? 0 }}">
                                            </p>


                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="showModel" > <i class="fa fa-check"></i> Update</button>
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
                                    {{ $note->user->name ?? '' }}
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

                    var supplierNew = $('.supplier_id').val();
                    //alert(supplierNew);
                    if (supplierNew != null) {
                        var insert = [], orderItem = [], nonArrayData = "";
                        $('#newRow tr').each(function () {
                            var currentRow = $(this).closest("tr");
                            if (validateRow(currentRow)) {
                                orderItem =
                                    {
                                        id: currentRow.find('.detail_Id').val(),
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
                            } else {
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
                            Id: Id,
                            PurchaseNumber: $('#PurchaseNumber').val(),
                            referenceNumber: $('#referenceNumber').val(),
                            PurchaseDate: $('#PurchaseDate').val(),
                            DueDate: $('#DueDate').val(),
                            Total: $('.total').val(),
                            subTotal: $('.rowTotal').val(),
                            totalVat: $('.TotalVat').val(),
                            grandTotal: $('.GTotal').val(),
                            paidBalance: cashPaid,
                            remainingBalance: $('#balance').val(),
                            supplier_id: $('#supplier_id').val(),
                            supplierNote: $('#PurchaseDescription').val(),
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
                                url: "{{ URL('purchaseUpdate') }}/" + Id,
                                type: "post",
                                data: Datas,
                                success: function (result) {
                                    if (result !== "Failed") {
                                        details = [];
                                        console.log(result);
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
                                //console.log(result);
                                $('#Address').text(result.Address);
                                $('#Mobile').text(result.Mobile);
                                $('#Email').text(result.postCode);
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
                            console.log(result);
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
    <script src="{{ asset('admin_assets/assets/dist/invoice/update_invoice.js') }}"></script>
@endsection
