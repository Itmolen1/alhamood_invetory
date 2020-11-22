<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Rizwan afridi">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('admin_assets/assets/images/favicon.png') }}">
    <title>Dashboard - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- This page CSS -->

    <!-- page CSS -->
    <link href="{{ asset('admin_assets/assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin_assets/assets/node_modules/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin_assets/assets/node_modules/switchery/dist/switchery.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin_assets/assets/node_modules/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin_assets/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin_assets/assets/node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin_assets/assets/node_modules/multiselect/css/multi-select.css') }}" rel="stylesheet" type="text/css" />

    <!-- chartist CSS -->
    <link href="{{ asset('admin_assets/assets/node_modules/morrisjs/morris.css') }}" rel="stylesheet">
    <!--Toaster Popup message CSS -->
    <link href="{{ asset('admin_assets/assets/node_modules/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
    <!--c3 plugins CSS -->
    <link href="{{ asset('admin_assets/assets/node_modules/c3-master/c3.min.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('admin_assets/assets/dist/css/style.min.css') }}" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="{{ asset('admin_assets/assets/dist/css/pages/dashboard1.css') }}" rel="stylesheet">

    <!-- page css -->
    <link href="{{ asset('admin_assets/assets/dist/css/pages/file-upload.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('admin_assets/assets/node_modules/dropify/dist/css/dropify.min.css') }}">

    <!-- summernotes CSS -->
    <link href="{{ asset('admin_assets/assets/node_modules/summernote/dist/summernote.css') }}" rel="stylesheet" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->

    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('admin_assets/assets/node_modules/jquery/jquery-3.2.1.min.js') }}"></script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        /*Profile Pic Start*/
        .picture-container{
            position: relative;
            cursor: pointer;
            text-align: center;
        }
        .picture{
            width: 150px;
            height: 150px;
            background-color: #999999;
            border: 4px solid #CCCCCC;
            color: #FFFFFF;
            border-radius: 50%;
            margin: 0px auto;
            overflow: hidden;
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
        }
        .picture:hover{
            border-color: #2ca8ff;
        }
        .content.ct-wizard-green .picture:hover{
            border-color: #05ae0e;
        }
        .content.ct-wizard-blue .picture:hover{
            border-color: #3472f7;
        }
        .content.ct-wizard-orange .picture:hover{
            border-color: #ff9500;
        }
        .content.ct-wizard-red .picture:hover{
            border-color: #ff3b30;
        }
        .picture input[type="file"] {
            cursor: pointer;
            display: block;
            height: 100%;
            left: 0;
            opacity: 0 !important;
            position: absolute;
            top: 0;
            width: 100%;
        }

        .picture-src{
            width: 100%;

        }
        /*Profile Pic End*/

        .steps-form-2 {
            display: table;
            width: 100%;
            position: relative; }
        .steps-form-2 .steps-row-2 {
            display: table-row; }
        .steps-form-2 .steps-row-2:before {
            top: 14px;
            bottom: 0;
            position: absolute;
            content: " ";
            width: 100%;
            height: 2px;
            background-color: #7283a7; }
        .steps-form-2 .steps-row-2 .steps-step-2 {
            display: table-cell;
            text-align: center;
            position: relative; }
        .steps-form-2 .steps-row-2 .steps-step-2 p {
            margin-top: 0.5rem; }
        .steps-form-2 .steps-row-2 .steps-step-2 button[disabled] {
            opacity: 1 !important;
            filter: alpha(opacity=100) !important; }
        .steps-form-2 .steps-row-2 .steps-step-2 .btn-circle-2 {
            width: 70px;
            height: 70px;
            border: 2px solid #59698D;
            background-color: white !important;
            color: #59698D !important;
            border-radius: 50%;
            padding: 22px 18px 15px 18px;
            margin-top: -22px; }
        .steps-form-2 .steps-row-2 .steps-step-2 .btn-circle-2:hover {
            border: 2px solid #4285F4;
            color: #4285F4 !important;
            background-color: white !important; }
        .steps-form-2 .steps-row-2 .steps-step-2 .btn-circle-2 .fa {
            font-size: 1.7rem; }
    </style>
</head>

<body class="horizontal-nav skin-megna-dark fixed-layout">
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="loader">
        <div class="loader__figure"></div>
        <p class="loader__label">IT Molen</p>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar">
        <nav class="navbar top-navbar navbar-expand-md navbar-dark">
            <!-- ============================================================== -->
            <!-- Logo -->
            <!-- ============================================================== -->
            <div class="navbar-header">
                <a class="navbar-brand" href="/">
                    <!-- Logo icon --><b>
                        <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                        <!-- Dark Logo icon -->
                        <img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="homepage" class="dark-logo" />
                        <!-- Light Logo icon -->
                        <img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="homepage" class="light-logo" />
                    </b>
                    <!--End Logo icon -->
                    <!-- Logo text --><span>
                         <!-- dark Logo text -->
                         <img src="{{ asset('admin_assets/assets/images/logo-text.png') }}" alt="homepage" class="dark-logo" />
                        <!-- Light Logo text -->
                         <img src="{{ asset('admin_assets/assets/images/logo-text.png') }}" class="light-logo" alt="homepage" /></span> </a>
            </div>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <div class="navbar-collapse">
                <!-- ============================================================== -->
                <!-- toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav mr-auto">
                    <!-- This is  -->
                    <li class="nav-item d-md-none"> <a class="nav-link nav-toggler waves-effect waves-light" href="javascript:void(0)"><i class="ti-menu"></i></a></li>
                    <!-- ============================================================== -->
                    <!-- Comment -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="ti-email"></i>
                            <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                        </a>
                        <div class="dropdown-menu mailbox animated bounceInDown">
                            <span class="with-arrow"><span class="bg-primary"></span></span>
                            <ul>
                                <li>
                                    <div class="drop-title text-white bg-danger">
                                        <h4 class="m-b-0 m-t-5">0 New</h4>
                                        <span class="font-light">Messages</span>
                                    </div>
                                </li>

                                <li>
                                    <div class="message-center">
                                        <!-- Message -->
                                        <!--  <a href="javascript:void(0)">
                                             <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>
                                             <div class="mail-contnet">
                                                 <h5>Luanch Admin</h5> <span class="mail-desc">Just see the my new admin_assets!</span> <span class="time">9:30 AM</span> </div>
                                         </a> -->

                                    </div>
                                </li>
                                <li>
                                    <a class="nav-link text-center m-b-5" href="javascript:void(0);"> <strong>Check all</strong> <i class="fa fa-angle-right"></i> </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <!-- ============================================================== -->
                    <!-- End Comment -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Messages -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle waves-effect waves-dark" href="#" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="icon-note"></i>
                            <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                        </a>
                        <div class="dropdown-menu mailbox animated bounceInDown" aria-labelledby="2">
                            <span class="with-arrow"><span class="bg-danger"></span></span>
                            <ul>
                                <li>
                                    <div class="drop-title bg-primary text-white">
                                        <h4 class="m-b-0 m-t-5">0 New</h4>
                                        <span class="font-light">Notifications</span>
                                    </div>
                                </li>

                                <li>
                                    <div class="message-center">
                                        <!-- Message -->
                                        <!--  <a href="javascript:void(0)">
                                             <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>
                                             <div class="mail-contnet">
                                                 <h5>Luanch Admin</h5> <span class="mail-desc">Just see the my new admin_assets!</span> <span class="time">9:30 AM</span> </div>
                                         </a> -->

                                    </div>
                                </li>

                                <li>
                                    <a class="nav-link text-center link m-b-5" href="javascript:void(0);"> <b>See all</b> <i class="fa fa-angle-right"></i> </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <!-- ============================================================== -->
                    <!-- End Messages -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Search -->
                    <!-- ============================================================== -->
                    <li class="nav-item search-box"> <a class="nav-link waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
                        <form class="app-search">
                            <input type="text" class="form-control" placeholder="Search &amp; enter"> <a class="srh-btn"><i class="ti-close"></i></a>
                        </form>
                    </li>
                </ul>
                <ul class="navbar-nav my-lg-0">
                    <!-- ============================================================== -->
                    <!-- mega menu -->
                    <!-- ============================================================== -->

                    <!-- ============================================================== -->
                    <!-- End mega menu -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ asset('admin_assets/assets/images/users/1.jpg') }}" alt="user" class="img-circle" width="30"></a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                            <span class="with-arrow"><span class="bg-primary"></span></span>
                            <div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
                                <div class=""><img src="{{ asset('admin_assets/assets/images/users/1.jpg') }}" alt="user" class="img-circle" width="60"></div>
                                <div class="m-l-10">
                                    <h4 class="m-b-0">Rizwan Afridi</h4>
                                    <p class=" m-b-0">rizwan.afridi9@gmail.com</p>
                                </div>
                            </div>
                            <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                            <!--   <a class="dropdown-item" href="javascript:void(0)"><i class="ti-wallet m-r-5 m-l-5"></i> My Balance</a>
                              <a class="dropdown-item" href="javascript:void(0)"><i class="ti-email m-r-5 m-l-5"></i> Inbox</a> -->
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)"><i class="ti-settings m-r-5 m-l-5"></i> Account Setting</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out m-r-5 m-l-5"></i> {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <div class="dropdown-divider"></div>
                            <div class="p-l-30 p-10"><a href="javascript:void(0)" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>
                        </div>
                    </li>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <li class="nav-item right-side-toggle"> <a class="nav-link  waves-effect waves-light" href="javascript:void(0)"><i class="ti-settings"></i></a></li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <aside class="left-sidebar">
        <div class="nav-text-box align-items-center d-md-none">
            <span><img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="IT Molen template"></span>
            <a class="nav-lock waves-effect waves-dark ml-auto hidden-md-down" href="javascript:void(0)"><i class="mdi mdi-toggle-switch"></i></a>
            <a class="nav-toggler waves-effect waves-dark ml-auto hidden-sm-up" href="javascript:void(0)"><i class="ti-close"></i></a>
        </div>
        <!-- Sidebar scroll-->
        <div class="scroll-sidebar">

            < x-Navigation />

        </div>
        <!-- End Sidebar scroll-->
    </aside>
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->






    @yield('content')



{{--    <script>--}}
{{--        $('#summernote').summernote({--}}
{{--            placeholder: 'Descriptions ...',--}}
{{--            tabsize: 2,--}}
{{--            height: 100--}}
{{--        });--}}
{{--    </script>--}}







    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    <footer class="footer">
        Powered by <a href="https://itmolen.nl/">IT MOlen</a> | © wahidgoc 2018
    </footer>
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->

<!-- Bootstrap popper Core JavaScript -->
<script src="{{ asset('admin_assets/assets/node_modules/popper/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="{{ asset('admin_assets/assets/dist/js/perfect-scrollbar.jquery.min.js') }}"></script>
<!--Wave Effects -->
<script src="{{ asset('admin_assets/assets/dist/js/waves.js') }}"></script>
<!--Menu sidebar -->
<script src="{{ asset('admin_assets/assets/dist/js/sidebarmenu.js') }}"></script>
<!--Custom JavaScript -->
<script src="{{ asset('admin_assets/assets/dist/js/custom.min.js') }}"></script>
<!-- ============================================================== -->
<!-- This page plugins -->
<!-- ============================================================== -->
<!--morris JavaScript -->
<script src="{{ asset('admin_assets/assets/node_modules/raphael/raphael-min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/morrisjs/morris.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
<!--c3 JavaScript -->
<script src="{{ asset('admin_assets/assets/node_modules/d3/d3.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/c3-master/c3.min.js') }}"></script>
<!-- Popup message jquery -->
<script src="{{ asset('admin_assets/assets/node_modules/toast-master/js/jquery.toast.js') }}"></script>
<!-- Chart JS -->
<script src="{{ asset('admin_assets/assets/dist/js/dashboard1.js') }}"></script>



<!--stickey kit -->
<script src="{{ asset('admin_assets/assets/node_modules/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>

<!-- This is data table -->
<script src="{{ asset('admin_assets/assets/node_modules/datatables/jquery.dataTables.min.js') }}"></script>
<!-- start - This is for export functionality only -->
<!--   <script src="../../../../../cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
  <script src="../../../../../cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
  <script src="../../../../../cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
  <script src="../../../../../cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
  <script src="../../../../../cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script src="../../../../../cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
  <script src="../../../../../cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script> -->


<script src="{{ asset('admin_assets/assets/dist/js/pages/jasny-bootstrap.js') }}"></script>


<!-- ============================================================== -->
<!-- jQuery file upload -->
<script src="{{ asset('admin_assets/assets/node_modules/dropify/dist/js/dropify.min.js') }}"></script>

<script language="javascript">

    $(document).ready(function(){
// Prepare the preview for profile picture
        $("#wizard-picture").change(function(){
            readURL(this);
        });
    });
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#wizardPicturePreview').attr('src', e.target.result).fadeIn('slow');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>


<script>
    $(document).ready(function() {
        // Basic
        $('.dropify').dropify();

        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });

        // Used events
        var drEvent = $('#input-file-events').dropify();

        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });

        drEvent.on('dropify.errors', function(event, element) {
            console.log('Has Errors');
        });

        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function(e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        })
    });
</script>



<script src="{{ asset('admin_assets/assets/node_modules/summernote/dist/summernote.min.js') }}"></script>
<script>
    jQuery(document).ready(function() {

        $('.summernote').summernote({
            height: 350, // set editor height
            minHeight: null, // set minimum height of editor
            maxHeight: null, // set maximum height of editor
            focus: false // set focus to editable area after initializing summernote
        });

        $('.inline-editor').summernote({
            airMode: true
        });

    });

    window.edit = function() {
        $(".click2edit").summernote()
    },
        window.save = function() {
            $(".click2edit").summernote('destroy');
        }
</script>

<script>
    $(document).ready(function() {
        $('#myTable').DataTable();
        $(document).ready(function() {
            var table = $('#example').DataTable({
                "columnDefs": [{
                    "visible": false,
                    "targets": 2
                }],
                "order": [
                    [2, 'asc']
                ],
                "displayLength": 25,
                "drawCallback": function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;
                    api.column(2, {
                        page: 'current'
                    }).data().each(function(group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
                            last = group;
                        }
                    });
                }
            });
            // Order by the grouping
            $('#example tbody').on('click', 'tr.group', function() {
                var currentOrder = table.order()[0];
                if (currentOrder[0] === 2 && currentOrder[1] === 'asc') {
                    table.order([2, 'desc']).draw();
                } else {
                    table.order([2, 'asc']).draw();
                }
            });
        });
    });
    $('#example23').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
</script>


<script src="{{ asset('admin_assets/assets/node_modules/switchery/dist/switchery.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('admin_assets/assets/node_modules/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('admin_assets/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.js') }}" type="text/javascript"></script>
<script type="text/javascript" src="{{ asset('admin_assets/assets/node_modules/multiselect/js/jquery.multi-select.js') }}"></script>
<script>
    jQuery(document).ready(function() {
        // Switchery
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
            new Switchery($(this)[0], $(this).data());
        });
        // For select 2
        $(".select2").select2();
        $('.selectpicker').selectpicker();
        //Bootstrap-TouchSpin
        $(".vertical-spin").TouchSpin({
            verticalbuttons: true,
            verticalupclass: 'ti-plus',
            verticaldownclass: 'ti-minus'
        });
        var vspinTrue = $(".vertical-spin").TouchSpin({
            verticalbuttons: true
        });
        if (vspinTrue) {
            $('.vertical-spin').prev('.bootstrap-touchspin-prefix').remove();
        }
        $("input[name='tch1']").TouchSpin({
            min: 0,
            max: 100,
            step: 0.1,
            decimals: 2,
            boostat: 5,
            maxboostedstep: 10,
            postfix: '%'
        });
        $("input[name='tch2']").TouchSpin({
            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '$'
        });
        $("input[name='tch3']").TouchSpin();
        $("input[name='tch3_22']").TouchSpin({
            initval: 40
        });
        $("input[name='tch5']").TouchSpin({
            prefix: "pre",
            postfix: "post"
        });
        // For multiselect
        $('#pre-selected-options').multiSelect();
        $('#optgroup').multiSelect({
            selectableOptgroup: true
        });
        $('#public-methods').multiSelect();
        $('#select-all').click(function() {
            $('#public-methods').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function() {
            $('#public-methods').multiSelect('deselect_all');
            return false;
        });
        $('#refresh').on('click', function() {
            $('#public-methods').multiSelect('refresh');
            return false;
        });
        $('#add-option').on('click', function() {
            $('#public-methods').multiSelect('addOption', {
                value: 42,
                text: 'test 42',
                index: 0
            });
            return false;
        });
        $(".ajax").select2({
            ajax: {
                url: "https://api.github.com/search/repositories",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function(markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            // templateResult: formatRepo, // omitted for brevity, see the source of this page
            //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
        });
    });
</script>

</body>

</html>
