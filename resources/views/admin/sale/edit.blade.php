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


                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Pad #</th>
                                                <th style="width: 150px">Customer</th>
                                                <th style="width: 150px">Vehicle</th>
                                                <th style="width: 150px">Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>VAT</th>
                                                <th>Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td> <input type="date" name="saleDate" id="saleDate" class="form-control" placeholder=""></td>
                                                <td><input type="text" placeholder="Invoice Number" class="invoiceNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class="form-control customer">
                                                            <option value="0">Customer</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="vehicle" class="form-control vehicle">
                                                            <option value="0">Vehicle</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="Product" class="form-control product">
                                                            <option value="0" selected>Diesel</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="Quantity" class="quantity form-control">
                                                    <input type="hidden" placeholder="Total" class="total form-control">
                                                </td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="Price" class="price form-control"></td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="VAT" name="VAT" value="0" class="VAT form-control"></td>
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
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled=""></p>


                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled></p>

                                            <p>Cash Paid: <input type="text" value="0.00" class="form-control cashPaid"></p>

                                            <p>Balance: <input type="text" value="0.00" class="form-control balance"></p>


                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Invoice</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>


                            <div class="table-responsive" style="margin-top: 20px">
                                <table class="table color-table danger-table">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">Date</th>
                                        <th style="width: 150px">Pad #</th>
                                        <th style="width: 150px">Customer</th>
                                        <th style="width: 150px">Vehicle</th>
                                        <th style="width: 150px">Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>VAT</th>
                                        <th>Total Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Nigam</td>
                                        <td>Eichmann</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Nigam</td>
                                        <td>Eichmann</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                        <td>@Sonu</td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>

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
        });







    </script>

    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>


@endsection
