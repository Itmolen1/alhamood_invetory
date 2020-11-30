@extends('shared.layout-admin')
@section('title', 'Loan Edit')

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
                    <h4 class="text-themecolor">Loan Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Loan</li>
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
                            <h4 class="m-b-0 text-white">Loan</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('loans.update', $loan->id) }}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Select Employee/ Customer</label>
                                                <select class="form-control custom-select loanTo" name="loanTo">
                                                   {{--  <option>--Select Employee / Customer Type--</option> --}}
                                                    <option value="employee" {{ ($loan->loanTo == 'employee') ? 'selected':'' }}>Employee</option>
                                                    <option value="customer" {{ ($loan->loanTo == 'customer') ? 'selected':'' }}>Customer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group employeeField">
                                                <label>Employee</label>
                                                <select class="form-control employee_Id" name="employee_id">
                                                    <option value="0">Employee</option>
                                                    @foreach($employees as $employee)
                                                        <option value="{{ $employee->id }}" {{ ($employee->id == $loan->employee_id) ? 'selected':''}}>{{ $employee->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group customerField">
                                                <label>Customer</label>
                                                <select class="form-control customer_Id" name="customer_id">
                                                    <option value="0">Customer</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $loan->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/row-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Remaining loan</label>
                                                <input type="text" name="" value="{{ $loan->remainingLoan }}" class="form-control" placeholder="Enter Remaining Loan" disabled="">
                                                 <input type="hidden" name="remainingLoan" value="{{ $loan->remainingLoan }}" class="form-control" placeholder="Enter Remaining Loan">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Payment Type</label>
                                                @if ($loan->isPay == true)
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="customRadio1" value="isPay" name="loanPayment" class="custom-control-input cash" checked="checked">
                                                        <label class="custom-control-label" for="customRadio1">Loan Payment</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="customRadio2" value="isReturn" name="loanPayment" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadio2">Loan Return</label>
                                                    </div>
                                                @elseif($loan->isReturn == true)
                                                     <div class="custom-control custom-radio">
                                                            <input type="radio" id="customRadio1" value="isPay" name="loanPayment" class="custom-control-input cash" checked="checked">
                                                            <label class="custom-control-label" for="customRadio1">Loan Payment</label>
                                                        </div>
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" id="customRadio2" value="isReturn" name="loanPayment" class="custom-control-input" checked="checked">
                                                            <label class="custom-control-label" for="customRadio2">Loan Return</label>
                                                    </div>
                                                @endif
                                                
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>
                                    <!--/row-->


                                    <div class="row">
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Pay Loan</label>
                                                <input type="text" name="payLoan" onkeyup="toWords($('.amount').val())" onClick="this.setSelectionRange(0, this.value.length)"  value="{{ $loan->payLoan }}" class="form-control amount" placeholder="Pay Loan">
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">In Words</label>
                                                <input type="text" name="loanInWords" id="SumOf" value="{{ $loan->loanInWords }}" value="" class="form-control SumOf" placeholder="In Words">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Voucher Number</label>
                                                <input type="text" name="voucherNumber" value="{{ $loan->voucherNumber }}" value="" class="form-control" placeholder="Enter Voucher">
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Date</label>
                                                <input type="date" name="loanDate" value="{{ $loan->loanDate }}" class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $loan->Description }}</textarea>
                                            </div>
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
            var val1 = $('.loanTo').val();
            
            if (val1 === 'employee') 
            {
                $('.employeeField').show();
                $('.customerField').hide();
            }
            else if(val1 === 'customer')
            {
                $('.employeeField').hide();
                $('.customerField').show();
            }
        });

        $(document).on("change", '.loanTo', function () {
            var val = $('.loanTo').val();

            if (val === 'employee'){
                $('.employeeField').show();
                $('.customerField').hide();
            }
            else if(val === 'customer')
            {
                $('.employeeField').hide();
                $('.customerField').show();
            }
            else
            {

                $('.employeeField').hide();
                $('.customerField').hide();
            }
        });


         /////////////////////////// customer select /////////////////
        $(document).ready(function () {

            $('.customer_Id').change(function () {
                // alert();
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ URL('customerRemaining') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                             if (result !== "Failed") {
                                    //console.log(result);
                                    $('.remainingLoan').val(result);

                                }  else {
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

         /////////////////////////// customer select /////////////////
        $(document).ready(function () {

            $('.employee_Id').change(function () {
                // alert();
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ URL('employeeRemaining') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                             if (result !== "Failed") {
                                    console.log(result);
                                    $('.remainingLoan').val(result);

                                }  else {
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

    </script>
     <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>


@endsection
