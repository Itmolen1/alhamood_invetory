@extends('shared.layout-admin')
@section('title', $title)
@section('content')
    <link href="{{ asset('admin_assets/assets/dist/css/jquery.dataTables.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin_assets/assets/node_modules/datatables/Buttons-1.6.5/css/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">{{$title}}</h4>
                            <div class="table-responsive m-t-40">
                                {!! $html !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#report_table').DataTable(
                {
                    pageLength: 50,
                    dom: 'Blfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    "footerCallback": function ( row, data, start, end, display ) {
                        var api = this.api(), data;

                        // Remove the formatting to get integer data for summation
                        var intVal = function ( i ) {
                            return typeof i === 'string' ?
                                i.replace(/[\$,]/g, '')*1 :
                                typeof i === 'number' ?
                                    i : 0;
                        };

                        // Total over all pages
                        total = api
                            .column( 5 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Total over this page
                        pageTotal = api
                            .column( 5, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Update footer
                        $( api.column( 5 ).footer() ).html(
                            '$'+pageTotal +' ( $'+ total +' total)'
                        );
                    }
                }
            );
            $('.dataTables_length').addClass('bs-select');
        });
    </script>

@endsection
