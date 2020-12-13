@extends('shared.layout-admin')
@section('title', 'Customer advances Edit')

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
                            <li class="breadcrumb-item active">customer Advances</li>
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
                            <h4 class="m-b-0 text-white">Customer</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer_advances.update', $customerAdvance->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Selection</label>
                                                <select class="form-control custom-select customer_id select2" name="customer_id" id="customer_id">
                                                    <option>--Select Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $customerAdvance->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Recept Numner</label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" value="{{ $customerAdvance->receiptNumber }}" class="form-control" placeholder="Receipt Number">
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
                                                <label>Payment Type {{ $customerAdvance->paymentType }}</label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option value="bankTransfer" {{ ($customerAdvance->paymentType == 'bankTransfer') ? 'selected':'' }}>Bank Transfer</option>
                                                    <option id="cash" value="cash" {{ ($customerAdvance->paymentType == 'cash') ? 'selected':'' }}>Cash</option>
                                                    <option value="checkTransfer" {{ ($customerAdvance->paymentType == 'checkTransfer') ? 'selected':'' }}>Check Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Amount</label>
                                                <input type="text" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" id="amount" value="{{ $customerAdvance->Amount }}" name="amount" class="form-control amount" placeholder="Enter Amount">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Sum Of</label>
                                                <input type="text" id="SumOf" name="amountInWords" value="{{ $customerAdvance->sumOf }}" class="form-control SumOf" placeholder="Amount In words">
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
                                                        <option value="{{ $bank->id }}" {{ ($bank->id == $customerAdvance->bank_id) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" value="{{ $customerAdvance->accountNumber }}" class="form-control" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Transfer Date</label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ $customerAdvance->TransferDate }}" class="form-control" placeholder="">
                                            </div>
                                        </div>

                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Register Date</label>
                                                <input type="date" id="registerDate" name="registerDate" value="{{ $customerAdvance->registerDate }}"  class="form-control" placeholder="">
                                            </div>
                                        </div>
                                        <!--/span-->


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receiver Name</label>
                                                <input type="text" id="receiver" name="receiverName" value="{{ $customerAdvance->receiverName }}" class="form-control" placeholder="Enter Receiver Name">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $customerAdvance->Description }}</textarea>
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


    </script>

    <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>




@endsection
