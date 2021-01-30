@extends('shared.layout-admin')
@section('title', 'ADD Customer advances')

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
                    <h4 class="text-themecolor">Customer Advances Registration</h4>
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
                            <h4 class="m-b-0 text-white">Customer Advance</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer_advances.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">ADD CUSTOMER ADVANCE</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Selection :- <span class="required">*</span></label>
                                                <select class="form-control custom-select customer_id select2" name="customer_id" id="customer_id">
                                                    <option disabled readonly="" selected>--Select your Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('customer_id'))
                                                    <span class="text-danger">{{ $errors->first('customer_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receipt Number :- <span class="required">*</span></label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option disabled readonly="" selected>--Select your Payment Type--</option>
                                                    <option value="bank">Bank</option>
                                                    <option id="cash" value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                                @if ($errors->has('paymentType'))
                                                    <span class="text-danger">{{ $errors->first('paymentType') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row bankTransfer">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Bank Name :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option value="">--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Account Number :- <span class="required">*</span></label>
                                                <input type="text" id="accountNumber" name="accountNumber" class="form-control" placeholder="Enter Account Number">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Cheque or Ref. Number ?</label>
                                                <input type="text" id="ChequeNumber" name="ChequeNumber" class="form-control" placeholder="Enter Cheque Number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Amount <span class="required">*</span></label>
                                                <input type="text" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" id="amount" name="amount" class="form-control amount" placeholder="Enter Amount">
                                                @if ($errors->has('amount'))
                                                    <span class="text-danger">{{ $errors->first('amount') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <label class="control-label">Sum Of <span class="required">*</span></label>
                                                <input type="text" id="SumOf" name="amountInWords" class="form-control SumOf" placeholder="Amount In words" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Register Date</label>
                                                <input type="date" id="registerDate" name="registerDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receiver Name <span class="required">*</span></label>
                                                <input type="text" id="receiver" name="receiverName" class="form-control" placeholder="Enter Receiver Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                        <button type="button" class="btn btn-inverse">Cancel</button>
                                    </div>
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

            $('.bankTransfer').hide();

        });

        $(document).on("change", '#paymentType', function () {
            var cashDetails = $('#paymentType').val();

           if (cashDetails === 'bank'){
               $('.bankTransfer').show();
           }
           else if(cashDetails === 'cheque')
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
