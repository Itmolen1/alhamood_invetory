@extends('shared.layout-admin')
@section('title', 'Meter Reading')

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
                    <h4 class="text-themecolor">Edit Meter Reading</h4>
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
                                            <input type="date" value="{{ $meter_details[0]->meter_reading->readingDate }}" id="meterReadingDate" class="form-control">
                                            <input type="hidden" value="{{ $meter_details[0]->meter_reading->id }}" id="id" class="id">
                                        </div>
                                    </div>
                                    <tr class="table-responsive">
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
                                                <th> <span class="actionText">Action</span> <input class=" btn btn-default addRow AddrowHead" id="addRow" type="button" value="+" /></th>
                                                
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($meter_details as $details)
                                                @if(!is_null($details->deleted_at))
                                                    <tr style="text-decoration: line-through; color:red">
                                                    <td><input type="text" value="{{ $details->meter_reader->Name ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->startReading ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->endReading ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->netReading ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->Purchases ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->Sales ?? 0 }}" class="form-control"></td>
                                                    <td><input type="text" value="{{ $details->Description ?? "" }}" class="form-control"></td>
                                                        <td><input class=" btn btn-danger" type="button" value="Deleted" /></td>
                                                    </tr>

                                                    {{--<tr style="text-decoration: line-through; color:red">--}}
                                                        {{--<td>Etart Pad: <input type="text" value="{{ $meter_details[0]->meter_reading->startPad }}"  class="form-control"></td>--}}
                                                        {{--<td>End Pad<input type="text" value="{{ $meter_details[0]->meter_reading->endPad }}" class="form-control"></td>--}}
                                                        {{--<td  colspan="2">Meter Sale:<input type="text" value="{{ $meter_details[0]->meter_reading->totalMeterSale }}" class="form-control"></td>--}}
                                                        {{--<td  colspan="2">Pad Sale<input type="text" value="{{ $meter_details[0]->meter_reading->totalPadSale }}" class="form-control"></td>--}}
                                                        {{--<td  colspan="2">Difference<input type="text" value="{{ $meter_details[0]->meter_reading->saleDifference }}"  class="form-control"></td>--}}
                                                    {{--</tr>--}}

                                                @endif
                                            @endforeach
                                            </tbody>

                                            <tbody id="newRow">
                                            @foreach($meter_details as $details)
                                            @if(is_null($details->deleted_at))
                                                <tr>
                                                    <td>
                                                        <div class="form-group">
                                                            <select name="meter_id" class="form-control meter_id">
                                                                <option value="0" readonly disabled selected>Meter</option>
                                                                @foreach($meter_readers as $reader)
                                                                    <option value="{{ $reader->id }}" {{ ($reader->id == $details->meter_reader_id) ? 'selected':'' }}>{{ $reader->Name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->startReading }}" placeholder="Start Reading" class="startReading form-control"></td>
                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->endReading }}" placeholder="End Reading" class="endReading form-control"></td>
                                                    <td><input type="text" value="{{ $details->netReading }}" placeholder="Net Reading" class="netReading form-control" disabled>
                                                        <input type="hidden" value="{{ $details->netReading }}" placeholder="Net Reading" class="netReading form-control" ></td>
                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Purchases }}" placeholder="Purchases" class="purchases form-control">
                                                        <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Sales }}" placeholder="Total Row Sale" class="totalRow form-control">
                                                        <input type="hidden" value="{{ $details->id }}" placeholder="" class="detail_id form-control">
                                                    </td>
                                                    <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Sales }}" placeholder="Sales" class="sales form-control" disabled>
                                                        <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $details->Sales }}" placeholder="Sales" class="sales form-control">
                                                    </td>
                                                    <td><input type="text" value="{{ $details->Description }}" placeholder="Net Description" class="Description form-control"></td>
                                                    <td><input class=" btn btn-danger remove" id="remove" type="button" value="X" /></td>
                                                </tr>
                                            @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <button type="button" class="btn btn-success" id="showUpdateModel" > <i class="fa fa-eye"></i> Update Notes</button>

                                        </div>

                                        <div class="col-md-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>Start Pad: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $meter_details[0]->meter_reading->startPad ?? 0 }}" class="form-control startPad"></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p>End Pad: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $meter_details[0]->meter_reading->endPad ?? 0 }}" class="form-control endPad"></p>
                                                </div>
                                            </div>

                                            <p>Total Meter Reading Sale: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $meter_details[0]->meter_reading->totalMeterSale ?? 0 }}" class="form-control totalSale" disabled>
                                                <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $meter_details[0]->meter_reading->totalMeterSale ?? 0 }}" class="form-control totalSale">
                                            </p>

                                            <p>Total Pad Sale: <input type="text" value="{{ $meter_details[0]->meter_reading->totalPadSale ?? 0 }}" class="form-control totalPad" disabled>
                                                <input type="hidden" value="{{ $meter_details[0]->meter_reading->totalPadSale ?? 0 }}" class="form-control totalPad">
                                            </p>


                                            <p>Difference: <input type="text" value="{{ $meter_details[0]->meter_reading->saleDifference ?? 0 }}" class="form-control balance" disabled>
                                                <input type="hidden" value="{{ $meter_details[0]->meter_reading->saleDifference ?? 0 }}" class="form-control balance">
                                            </p>


                                        </div>
                                    </div>


                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="showModel"> <i class="fa fa-check"></i> Update</button>
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

    <div class="modal fade" id="updateMessage" tabindex="-1" role="dialog" aria-labelledby="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="message-texta" class="control-label">Update Note:</label>
                            <textarea class="form-control" id="UpdateDescription" placeholder="Update Note"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input class="btn btn-info" id="submit"  type="button" value="Update Purchase">
                    {{--                    <button type="button" class="btn btn-info">Send message</button>--}}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ShowUpdates" tabindex="-1" role="dialog" aria-labelledby="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <table class="table color-bordered-table success-bordered-table">
                        <thead>
                        <tr>

                            <th>User Name</th>
                            <th>Description</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($update_notes as $note)
                            <tr>
                                <td>
                                    {{ $note->user->name ?? '' }}
                                </td>
                                <td>{{ $note->Description }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">

                    {{--                    <button type="button" class="btn btn-info">Send message</button>--}}
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {

            $('#showUpdateModel').click(function () {
                $('#ShowUpdates').modal();
            });

            $('#showModel').click(function () {
                $('#updateMessage').modal();
            });

            /////////////// Add Record //////////////////////
            $('#submit').click(function () {

                $('#submit').text('please wait...');
                $('#submit').attr('disabled',true);

                var meter_id = $('.meter_id').val();
                //alert(supplierNew);
                if (meter_id != null)
                {
                    var insert = [], orderItem = [], nonArrayData = "";
                    $('#newRow tr').each(function () {
                        var currentRow = $(this).closest("tr");
                        if (validateRow(currentRow)) {
                            orderItem =
                                {
                                    meter_id: currentRow.find('.meter_id').val(),
                                    detail_id: currentRow.find('.detail_id').val(),
                                    startReading: currentRow.find('.startReading').val(),
                                    endReading: currentRow.find('.endReading').val(),
                                    netReading: currentRow.find('.netReading').val(),
                                    purchases: currentRow.find('.purchases').val(),
                                    sales: currentRow.find('.sales').val(),
                                    Description: currentRow.find('.Description').val(),
                                };
                            insert.push(orderItem);
                        }
                        else
                        {
                            return false;
                        }

                    });
                    var Id = $('#id').val();
                    let details = {
                        Id:Id,
                        meterReadingDate: $('#meterReadingDate').val(),
                        startPad: $('.startPad').val(),
                        endPad: $('.endPad').val(),
                        totalSale: $('.totalSale').val(),
                        totalPad: $('.totalPad').val(),
                        balance: $('.balance').val(),
                        UpdateDescription: $('#UpdateDescription').val(),
                        orders: insert,
                    }
                    // var Datas = {Data: details}
                    // console.log(Datas);
                    if (insert.length > 0) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Datas = {Data: details};
                        console.log(Datas);
                        $.ajax({
                            url: "{{ URL('meterReadingUpdate') }}/" + Id,
                            type: "post",
                            data: Datas,
                            success: function (result) {
                                if (result !== "Failed") {
                                    details = [];
                                    //console.log(result);
                                    alert("Data Inserted Successfully");
                                    window.location.href = "{{ route('meter_readings.index') }}";
                                } else {
                                    alert(result);
                                }
                            },
                            error: function (errormessage) {
                                alert(errormessage);
                            }
                        });
                    } else
                    {
                        alert('Please Add item to list');
                        $('#submit').text('Save');
                        $('#submit').attr('disabled',false);
                    }
                }
                else
                {
                    alert('Select Meter first')
                    $('#submit').text('Save');
                    $('#submit').attr('disabled',false);
                }

            });
            //////// end of submit Records /////////////////



/////////////////// change date //////////////////////////
            $('#meterReadingDate').change(function () {
                // alert($(this).val());

                // var Id = 0;
                var Id = $(this).val();

                $.ajax({
                    // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url: "{{ URL('getSalesByDate') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    statusCode: {
                        500: function() {
                            alert("No Data Available On same Date");
                            $('.startPad').val('');
                            $('.endPad').val('');
                            $('.totalPad').val('');
                        }
                    },
                    success: function (result) {
                        if (result !== "Failed") {
                            console.log(result);
                            $('.startPad').val(result.firstPad);
                            $('.endPad').val(result.lastPad);
                            $('.totalPad').val((result.total));

                        }
                    },
//                                error: function (errormessage) {
//                                    alert(errormessage);
//                                }
                });
            });




            // ///////////////////// Add new Row //////////////////////
            $(document).on("click",'.addRow', function () {

                var currentRow = $(this).closest("tr");
                if (validateRow(currentRow))
                {
                    $('.addRow').removeAttr("value", "");
                    $('.addRow').attr("value", "X");
                    $('.addRow').removeClass('btn-success').addClass('btn-danger');
                    $('.addRow').removeClass('addRow').addClass('remove');
                    $('.AddrowHead').hide();
                    $('.actionText').show();

                    var html = '';
                    html += '<tr>';
                    html += '<td><select name="meter_id" class="meter_id form-control"><option value="0" readonly disabled selected>Meter</option>@foreach($meter_readers as $reader)<option value="{{ $reader->id }}">{{ $reader->Name }}</option>@endforeach</select></td>';
                    html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Start Reading" class="startReading form-control"></td>';
                    html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="End Reading" class="endReading form-control"></td>';
                    html += '<td><input type="text" value="0.00" placeholder="Net Reading" class="netReading form-control" disabled><input type="hidden" value="0.00" placeholder="Net Reading" class="netReading form-control" ></td>';
                    html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Purchases" class="purchases form-control"><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Total Row Sale" class="totalRow form-control"></td>';
                    html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Sales" class="sales form-control" disabled><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Sales" class="sales form-control"></td>';
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
                CountTotal()
            });
            // /////////////end remove row //////////////

        });

    </script>

    <script src="{{ asset('admin_assets/assets/dist/invoice/meterReading.js') }}"></script>


@endsection
