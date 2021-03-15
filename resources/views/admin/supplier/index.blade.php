@extends('shared.layout-admin')
@section('title', 'Suppliers')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">supplier</li>
                        </ol>
                        <a href="{{ route('suppliers.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New supplier</button></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Suppliers</h4>
                            <h6 class="card-subtitle">All Suppliers</h6>
                            <div class="table-responsive m-t-40">
                                <table id="suppliers_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>TRN</th>
                                        <th>Category</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>

                                   {{--  <tbody>
                                    @foreach($suppliers as $supplier)
                                        <tr>
                                            <td>{{ $supplier->Name }}</td>
                                            <td>{{ $supplier->Mobile }}</td>
                                            <td>{{ $supplier->paymentType }}</td>
                                            <td>{{ $supplier->Address }}</td>
                                            <td>
                                                @if($supplier->isActive == true)
                                                    Active
                                                @else
                                                    UnActive
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('suppliers.destroy',$supplier->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                <a href="{{ route('suppliers.edit', $supplier->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody> --}}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="text-align: center !important;">

                        <h2 class="modal-title" >Confirmation</h2>
                    </div>
                    <div class="modal-body">
                        <h4 align="center" style="margin:0;">Are you sure you want to remove this data?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
        </div>
    </div>

   <script>
        $(document).ready(function () {
            $('#suppliers_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('suppliers.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Mobile',
                        name: 'Mobile'
                    },
                    {
                        data: 'TRNNumber',
                        name: 'TRNNumber'
                    },
                    {
                        data: 'companyType',
                        name: 'companyType'
                    },
                    {
                        data: 'Address',
                        name: 'Address'
                    },
                    {
                        data: 'isActive',
                        name: 'isActive',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });
        });
    </script>
    <script>
        function ConfirmDelete()
        {
         var result = confirm("Are you sure you want to delete?");
         if (result) {
            document.getElementById("deleteData").submit();
         }
        }
    </script>
@endsection
