@extends('shared.layout-admin')
@section('title', 'Meter Reading Edit')

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
                    <h4 class="text-themecolor">Meter Reading</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Meter</li>
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

                                    <div class="row py-2">
                                        <div class="col-md-8">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 150px">Meter</th>
                                                <th style="width: 150px">Start Reading</th>
                                                <th style="width: 150px">End Reading</th>
                                                <th style="width: 150px">Net Reading</th>
                                                <th style="width: 150px">Purchases</th>
                                                <th style="width: 150px">Sales</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="meter_id" class="form-control customer">
                                                            <option value="0">Meter</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="Start Reading" class="startReading form-control"></td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="End Reading" class="endReading form-control"></td>
                                                <td><input type="text" value="0.00" placeholder="Net Reading" class="netReading form-control" disabled>
                                                    <input type="hidden" value="0.00" placeholder="Net Reading" class="netReading form-control" ></td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="Purchases" class="purchases form-control">
                                                    <input type="hidden" onfocus="this.value=''" value="0.00" placeholder="Total Row Sale" class="totalRow form-control">
                                                </td>
                                                <td><input type="text" onfocus="this.value=''" value="0.00" placeholder="Sales" class="sales form-control" disabled>
                                                    <input type="hidden" onfocus="this.value=''" value="0.00" placeholder="Sales" class="sales form-control">
                                                </td>
                                                <td><input type="text" placeholder="Net Description" class="Description form-control"></td>
                                                <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">

                                        </div>

                                        <div class="col-md-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>Start Pad: <input type="text" value="0.00" class="form-control "></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p>End Pad: <input type="text" value="0.00" class="form-control "></p>
                                                </div>
                                            </div>

                                            <p>Total Meter Reading Sale: <input type="text" value="0.00" class="form-control totalSale"></p>

                                            <p>Total Pad Sale: <input type="text" value="200" class="form-control tatalPad" disabled>
                                                <input type="hidden" value="20" class="form-control tatalPad">
                                            </p>


                                            <p>Difference: <input type="text" value="0.00" class="form-control balance" disabled>
                                                <input type="hidden" value="0.00" class="form-control balance">
                                            </p>


                                        </div>
                                    </div>


                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Meter Reading</button>
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

            // ///////////////////// Add new Row //////////////////////
            $(document).on("click",'.addRow', function () {

                var currentRow = $(this).closest("tr");

                {
                    $('.addRow').removeAttr("value", "");
                    $('.addRow').attr("value", "X");
                    $('.addRow').removeClass('btn-success').addClass('btn-danger');
                    $('.addRow').removeClass('addRow').addClass('remove');

                    var html = '';
                    html += '<tr>';
                    html += '<td><select name="meter_id" class="meter_id form-control"> <option value="2">Meter</option> </select></td>';
                    html += '<td><input type="text" onfocus="this.value=\'\'" value="0.00" placeholder="Start Reading" class="startReading form-control"></td>';
                    html += '<td><input type="text" onfocus="this.value=\'\'" value="0.00" placeholder="End Reading" class="endReading form-control"></td>';
                    html += '<td><input type="text" value="0.00" placeholder="Net Reading" class="netReading form-control" disabled><input type="hidden" value="0.00" placeholder="Net Reading" class="netReading form-control" ></td>';
                    html += '<td><input type="text" onfocus="this.value=\'\'" value="0.00" placeholder="Purchases" class="purchases form-control"><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Total Row Sale" class="totalRow form-control"></td>';
                    html += '<td><input type="text" onfocus="this.value=\'\'" value="0.00" placeholder="Sales" class="sales form-control" disabled><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Sales" class="sales form-control"></td>';
                    html += '<td><input type="text" placeholder="Net Description" class="Description form-control"></td>';
                    html += '<td><input class="btn btn-success addRow" id="addRow" type="button" value="+" /></td>';
                    html += '</tr>';
                    $('#newRow').append(html);
                }

            });
            ///////// end of add new row //////////////
            ////////////// Remove row ///////////////
            $(document).on("click",'.remove', function () {
                var Current = $(this).closest('tr');
                Current.remove();
            });
            // /////////////end remove row //////////////


            //// accept Only Numbers /////////////////////


            //////// end Accept only Number ////////////////////
        });


        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.startReading', function () {
            var Currentrow = $(this).closest("tr");
            var startReading = $(this).val();
            if (parseInt(startReading) >= 0)
            {
                var sum1 = parseFloat(Currentrow.find('.endReading').val()) - parseInt(startReading);
                // var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
                //alert(Total);
                Currentrow.find('.netReading').val(sum1);
                // Currentrow.find('.sales').val(sum) ;
                Currentrow.find('.totalRow').val(sum1) ;
                var pur = Currentrow.find('.purchases').val();
                pchase(pur, Currentrow);
                CountTotal()

            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.endReading', function () {
            var Currentrow = $(this).closest("tr");
            var endReading = $(this).val();
            if (parseInt(endReading) >= 0)
            {
                var sum1 = parseInt(endReading) - parseFloat(Currentrow.find('.startReading').val())
                //var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
                //alert(Total);
                Currentrow.find('.netReading').val(sum1);
                //Currentrow.find('.sales').val(sum) ;
                Currentrow.find('.totalRow').val(sum1) ;
                var pur = Currentrow.find('.purchases').val();
                pchase(pur, Currentrow);
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.purchases', function () {
            var Currentrow = $(this).closest("tr");
            var purchases = $(this).val();
            if (parseInt(purchases) >= 0)
            {
                var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(purchases);
                //alert(Total);
                Currentrow.find('.sales').val(sum) ;
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.sales', function () {
            var Currentrow = $(this).closest("tr");
            var sales = $(this).val();
            if (parseInt(sales) >= 0)
            {
                var sum = parseInt(sales) - parseFloat(Currentrow.find('.purchases').val());
                //alert(Total);
                Currentrow.find('.netDifference').val(sum);
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////


        //////////// tatal  /////////////////
        function CountTotal() {
            var Totalvalue = 0;
            var Gtotal = 0;
            $('#newRow tr').each(function () {
                if ($(this).find(".sales").val().trim() !== ""){
                    Gtotal = parseFloat(Gtotal) + parseFloat($(this).find(".sales").val());
                }
                else {
                    Gtotal = parseFloat(Gtotal);
                }
            });

            $('.totalSale').val((Gtotal.toFixed(2)));
            var Input = parseFloat(Gtotal - $('.tatalPad').val());
            $('.balance').val((Input.toFixed(2)));

        }
        //////////////// end of total  /////////////

        //////////// tatal  /////////////////
        function pchase(pur, Currentrow) {
            var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(pur);
            //alert(Total);
            Currentrow.find('.sales').val(sum) ;
            CountTotal()
        }
        //////////////// end of total  /////////////

        //////////////balance ////////////////////
        // $(document).on("keyup",'.tatalPad',function () {
        //     var GTotal = $('.totalSale').val();
        //     var Input = parseFloat(GTotal - $('.tatalPad').val());
        //     var rr= $('.balance').val((Input.toFixed(2)));
        // });
        ////////////// balance end ///////////////////////






    </script>

    {{--    <script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>--}}


@endsection
