@extends('shared.layout-admin')
@section('title', 'Supplier Edit')

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
                    <h4 class="text-themecolor">Supplier Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">supplier</li>
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
                            <form method="post" action="{{ route('suppliers.update', $supplier->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Company Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" value="{{ $supplier->Name }}" class="form-control" placeholder="Enter Customer Company Name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Owner/Representative Name</label>
                                                <input type="text" id="Representative" value="{{ $supplier->Representative }}" name="Representative" class="form-control" placeholder="Enter Owner/Representative Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option readonly disabled="" selected="">--Select your Company Type--</option>
                                                    @foreach ($company_types as $company_type)
                                                        <option value="{{ $company_type->id }}" {{ ($company_type->id == $supplier->company_type_id) ? 'selected':'' }}>{{ $company_type->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration date</label>
                                                <input type="date" name="registrationDate" value="{{ $supplier->registrationDate }}" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select paymentType" name="paymentType">
                                                    <option readonly disabled="" selected="">--Select your Payment Type--</option>
                                                    @foreach ($payment_types as $payment)
                                                        <option value="{{ $payment->id }}" {{ ($payment->id == $supplier->payment_type_id) ? 'selected':'' }}>{{ $payment->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" id="paymentTermAll">
                                                <label class="control-label">Payment Term</label>
                                                 <select class="form-control custom-select" id="paymentTerm" tabindex="1">
                                                    <option readonly disabled="" selected="">--Select Payment Term--</option>
                                                    @foreach ($payment_terms as $term)
                                                       <option value="{{ $term->id }}" {{ ($term->id == $supplier->payment_term_id) ? 'selected':'' }}>{{ $term->Name }}</option>
                                                    @endforeach
                                                </select>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Opening Balance :- <span class="required">*</span></label>
                                                <input type="number" step=".01" name="openingBalance" value="{{ $supplier->openingBalance }}" class="form-control" placeholder="Opening Balance" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Opening Balance As of Date :- <span class="required">*</span></label>
                                                <input type="date" name="openingBalanceAsOfDate" class="form-control" value="{{ $supplier->openingBalanceAsOfDate }}" placeholder="Opening Balance As of Date" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">TRN Number</label>
                                                <input type="text" name="TRNNumber" value="{{ $supplier->TRNNumber }}" class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Upload File</label>
                                                <input type="file" name="fileUpload"  class="form-control" placeholder="Enter TRN Number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="Mobile" value="{{ $supplier->Mobile }}" placeholder="Mobile" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="Phone" value="{{ $supplier->Phone }}" placeholder="Phone" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="text" name="Email" value="{{ $supplier->Email }}" placeholder="Email" class="form-control">
                                            </div>
                                        </div>
                                    </div>


                                    <h3 class="box-title m-t-40">Address</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <label>Street</label>
                                                <input type="text" name="Address" value="{{ $supplier->Address }}" placeholder="Address" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Region :- <span class="required">*</span></label>
                                                <select class="form-control custom-select region_id" name="region_id" id="region_id">
                                                    <option value="">-- Select Region --</option>
                                                    @foreach($regions as $region)
                                                        @if(!empty($region->Name))
                                                            <option value="{{ $region->id }}" {{ ($region->id == $supplier->region_id) ? 'selected':'' }}>{{ $region->Name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" name="City" id="city" value="{{ $supplier->region->city->Name ?? "" }}" placeholder="City" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="State" id="state" value="{{ $supplier->region->city->state->Name ?? "" }}" PLACEHOLDER="State" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Post Code</label>
                                                <input type="text" name="postCode" value="{{ $supplier->postCode }}" placeholder="PostCode" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="Country" id="country" value="{{ $supplier->region->city->state->country->Name ?? "" }}" PLACEHOLDER="Country" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $supplier->Description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Supplier</button>
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
            var cash = $('.paymentType').val();

            if (cash === '2')
            {
                $('#paymentTermAll').show();
            }
            else
            {
                $('#paymentTermAll').hide();
            }

        });
        $(document).on("change", '.paymentType', function () {
            var cash = $('.paymentType').val();

            if (cash === '2'){
                $('#paymentTermAll').show();
            }
            else
            {
                $('#paymentTermAll').hide();
            }
        });
    </script>

    <script>
        /////////////////////////// location select /////////////////
        $(document).ready(function () {

            $('.region_id').change(function () {
                // alert();
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('locationDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                console.log(result);
                                $('#city').val(result.city.Name);
                                $('#state').val(result.city.state.Name);
                                $('#country').val(result.city.state.country.Name);
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
        ////////////// end of location select ////////////////
    </script>


@endsection
