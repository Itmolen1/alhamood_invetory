@extends('shared.layout-admin')
@section('title', 'Supplier advances Edit')

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
                    <h4 class="text-themecolor">Customer Advances Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">supplier Advances</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
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
                            <h4 class="m-b-0 text-white">Supplier</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('supplier_advances.update', $supplierAdvance->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Selection</label>
                                                <select class="form-control custom-select supplier_id select2" name="supplier_id" id="supplier_id">
                                                    <option>--Select your Customer--</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" {{ ($supplier->id == $supplierAdvance->supplier_id) ? 'selected':'' }}>{{ $supplier->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receipt Number</label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" value="{{ $supplierAdvance->receiptNumber }}" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type {{ $supplierAdvance->paymentType }}</label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option value="bankTransfer" {{ ($supplierAdvance->paymentType == 'bankTransfer') ? 'selected':'' }}>Bank Transfer</option>
                                                    <option id="cash" value="cash" {{ ($supplierAdvance->paymentType == 'cash') ? 'selected':'' }}>Cash</option>
                                                    <option value="checkTransfer" {{ ($supplierAdvance->paymentType == 'checkTransfer') ? 'selected':'' }}>Check Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Amount</label>
                                                <input type="text" onkeyup="toWords($('.amount').val())" id="amount" value="{{ $supplierAdvance->Amount }}" name="amount" class="form-control amount" placeholder="Enter Amount">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Sum Of</label>
                                                <input type="text" id="SumOf" name="amountInWords" value="{{ $supplierAdvance->sumOf }}" class="form-control SumOf" placeholder="Amount In words">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->


                                    <div class="row bankTransfer">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" {{ ($bank->id == $supplierAdvance->bank_id) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" value="{{ $supplierAdvance->accountNumber }}" class="form-control" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Transfer Date</label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ $supplierAdvance->TransferDate }}" class="form-control" placeholder="">
                                            </div>
                                        </div>

                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Register Date</label>
                                                <input type="date" id="registerDate" name="registerDate" value="{{ $supplierAdvance->registerDate }}"  class="form-control" placeholder="">
                                            </div>
                                        </div>
                                        <!--/span-->


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receiver Name</label>
                                                <input type="text" id="receiver" name="receiverName" value="{{ $supplierAdvance->receiverName }}" class="form-control" placeholder="Enter Receiver Name">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $supplierAdvance->Description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Advance</button>
                                        <button type="button" class="btn btn-inverse">Cancel</button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row -->

            <!-- ============================================================== -->
            <!-- End PAge Content -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
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


            var val = $('#paymentType').val();
            if (val !== 'cash'){
                $('.bankTransfer').show();
            }
            else {
                $('.bankTransfer').hide();
            }

        });

        $(document).on("change", '#paymentType', function () {
            var cashDetails = $('#paymentType').val();

            if (cashDetails === 'bankTransfer'){
                $('.bankTransfer').show();
            }
            else if(cashDetails === 'checkTransfer')
            {
                $('.bankTransfer').show();
            }
            else
            {
                $('.bankTransfer').hide();
            }
        });



        // American Numbering System
        var th = ['', 'thousand', 'million', 'billion', 'trillion'];
        // uncomment this line for English Number System
        // var th = ['','thousand','million', 'milliard','billion'];

        var dg = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        var tn = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        var tw = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        function toWords(s) {

            s = s.toString();
            s = s.replace(/[\, ]/g, '');
            if (s != parseFloat(s)) return 'not a number';
            var x = s.indexOf('.');
            var fulllength=s.length;

            if (x == -1) x = s.length;
            if (x > 15) return 'too big';
            var startpos=fulllength-(fulllength-x-1);
            var n = s.split('');

            var str = '';
            var str1 = ''; //i added another word called cent
            var sk = 0;
            for (var i = 0; i < x; i++) {
                if ((x - i) % 3 == 2) {
                    if (n[i] == '1') {
                        str += tn[Number(n[i + 1])] + ' ';
                        i++;
                        sk = 1;
                    } else if (n[i] != 0) {
                        str += tw[n[i] - 2] + ' ';

                        sk = 1;
                    }
                } else if (n[i] != 0) {
                    str += dg[n[i]] + ' ';
                    if ((x - i) % 3 == 0) str += 'hundred ';
                    sk = 1;
                }
                if ((x - i) % 3 == 1) {
                    if (sk) str += th[(x - i - 1) / 3] + ' ';
                    sk = 0;
                }
            }
            if (x != s.length) {

                str += 'and '; //i change the word point to and
                str1 += 'cents '; //i added another word called cent
                //for (var i = x + 1; i < y; i++) str += dg[n[i]] + ' ' ;
                var j=startpos;

                for (var i = j; i < fulllength; i++) {

                    if ((fulllength - i) % 3 == 2) {
                        if (n[i] == '1') {
                            str += tn[Number(n[i + 1])] + ' ';
                            i++;
                            sk = 1;
                        } else if (n[i] != 0) {
                            str += tw[n[i] - 2] + ' ';

                            sk = 1;
                        }
                    } else if (n[i] != 0) {

                        str += dg[n[i]] + ' ';
                        if ((fulllength - i) % 3 == 0) str += 'hundred ';
                        sk = 1;
                    }
                    if ((fulllength - i) % 3 == 1) {

                        if (sk) str += th[(fulllength - i - 1) / 3] + ' ';
                        sk = 0;
                    }
                }
            }
            var result=str.replace(/\s+/g, ' ') + str1;
            //return str.replace(/\s+/g, ' ');
            $('.SumOf').val(result);
            return result; //i added the word cent to the last part of the return value to get desired output

        }

    </script>


@endsection
