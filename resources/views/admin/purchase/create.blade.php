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
                    <h4 class="text-themecolor">purchase Registration</h4>
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
                            <h4 class="m-b-0 text-white">purchases</h4>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">

                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Vender Name</label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option>--Select Vender--</option>
                                                    <option>Transportation</option>
                                                    <option>Construction</option>
                                                    <option>Fuel traders</option>
                                                    <option>Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">purchase date</label>
                                                <input type="date" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>
                                    <!--/row-->

                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <ul class="feeds p-b-20">
                                                <li>Address <span class="text-muted">No Address</span></li>
                                                <li>Mobile <span class="text-muted">No Mobile</span></li>
                                                <li>Email <span class="text-muted">No Email</span></li>
                                                <li>TRN <span class="text-muted">No TRN</span></li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Due date</label>
                                                <input type="date" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Reference Number</label>
                                                        <input type="text" class="form-control" placeholder="Reference Number">
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
                                                <th style="width: 150px">purchase #</th>
                                                <th style="width: 150px">product</th>
                                                <th style="width: 100px">Unit</th>
                                                <th style="width: 200px">Description</th>
                                                <th>quantity</th>
                                                <th>Price</th>
                                                <th>VAT</th>
                                                <th>Total Amount</th>
                                                {{--                                                <th>Action</th>--}}
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td> <input type="date" name="saleDate" id="saleDate" class="form-control" placeholder=""></td>
                                                <td><input type="text" placeholder="Purchase Number" class="purchaseNumber form-control"></td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class="form-control customer">
                                                            <option value="0">product</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" placeholder="Unit" class="unit form-control"></td>
                                                <td><input type="text" placeholder="Description" class="discription form-control"></td>

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
                                                <input type="file">
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
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
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
            // $('#paymentTermAll').hide();
            //
            // $("#customRadio1 input:radio").click(function() {
            //
            //     alert("clicked");
            //
            // });

            //
            // $('.c1').click(function () {
            //     $('#paymentTermAll').show();
            // });
            // $('.c2').click(function () {
            //     $('#paymentTermAll').hide();
            // });
        });
    </script>

    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>


@endsection
