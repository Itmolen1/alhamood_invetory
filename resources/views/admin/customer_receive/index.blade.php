@extends('shared.layout-admin')
@section('title', 'New Payment')

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
                    <h4 class="text-themecolor">Payment</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Payment</li>
                        </ol>
                        <a href="" title=""><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button></a>
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

                                    <div class="row">
                                        <div class="col-md-11">
                                            <div class="form-group">
                                              {{--   <label>Select Customer</label> --}}
                                                <select class="form-control custom-select select2 customer_id" name="customer_id" id="customer_id">
                                                    <option> ---- Select Customers ---- </option>
                                                    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1 all">
                                            <input type="checkbox" class="form-control" name="chk[]" id="selectall">
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                            <th>Invoice</th>
                                            <th>Vehicle</th>
                                            <th>Total</th>
                                            <th>Pad</th>
                                            <th>Balance</th>
                                            <th>Date</th>
                                            <th width="70">Action</th>
                                                
                                            </tr>
                                            </thead>
                                            <tbody style="font-size: 12px">
                                            <tr>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> <input type="checkbox" class="singlechkbox" name="username" value="1"/></td>
                                            </tr>
                                            <tr>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                 <td> <input type="checkbox" class="singlechkbox" name="username" value="1"/></td>
                                            </tr>
                                            <tr>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                 <td> <input type="checkbox" class="singlechkbox" name="username" value="1"/></td>
                                            </tr>
                                            <tr>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                 <td> <input type="checkbox" class="singlechkbox" name="username" value="1"/></td>
                                            </tr>
                                            <tr>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                <td> 2323232</td>
                                                 <td> <input type="checkbox" class="singlechkbox" name="username" value="1"/></td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                     <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                              {{--   <label>Select Customer</label> --}}
                                                <select class="form-control custom-select select2 customer_id" name="customer_id" id="customer_id">
                                                    <option>---- Payment Type ----</option>
                                                    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="" id="" placeholder="Reference Number">
                                        </div>

                                         <div class="col-md-4">
                                            <input type="date" class="form-control" name="" id="" placeholder="">
                                        </div>
                                    </div>

                                     <div class="row">
                                         <div class="col-md-12">
                                            <textarea style="width: 100%" name=""></textarea> 
                                        </div>
                                    </div>

                                     <div class="row">
                                        <div class="col-md-4">
                                              <div class="form-group">
                                           <input type="text" class="form-control" name="" id="" placeholder="Total Amount">
                                          </div>
                                        </div>
                                        <div class="col-md-4">
                                           
                                        </div>

                                         <div class="col-md-4">
                                            <div class="form-group">
                                            <input type="text" class="form-control" name="" id="" placeholder="Paid Amount">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
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

 <script type="text/javascript">
 jQuery(function($) {
    $('body').on('click', '#selectall', function() {
        $('.singlechkbox').prop('checked', this.checked);
    });
 
    $('body').on('click', '.singlechkbox', function() {
        if($('.singlechkbox').length == $('.singlechkbox:checked').length) {
            $('#selectall').prop('checked', true);
        } else {
            $("#selectall").prop('checked', false);
        }
 
    });
 });
</script>

@endsection
