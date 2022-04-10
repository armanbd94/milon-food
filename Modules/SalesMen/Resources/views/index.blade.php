@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
                    @if (permission('sr-add'))
                    <a href="{{ url('sales-representative/add') }}" class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-plus-circle"></i> Add New
                    </a>
                    @endif
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.textbox labelName="Name" name="name" col="col-md-4" placeholder="Enter name" />
                        <x-form.textbox labelName="Phone No." name="phone" col="col-md-4" placeholder="Enter phone number" />
                        <x-form.textbox labelName="Email" name="email" col="col-md-4" placeholder="Enter email" />
                        <x-form.selectbox labelName="ASM" name="asm_id" required="required" col="col-md-4" class="selectpicker">
                            @if (!$asms->isEmpty())
                            @foreach ($asms as $asm)
                                <option value="{{ $asm->id }}">{{ $asm->name.' - '.$asm->phone }}</option>
                            @endforeach 
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required" col="col-md-4" class="selectpicker" onchange="getUpazilaList(1)">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach 
                                @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="District" name="district_id" col="col-md-4" class="selectpicker" onchange="getUpazilaList(1)">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $district)
                                    @if ($district->type == 1)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-4" class="selectpicker">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $upazila)
                                    @if ($upazila->type == 2)
                                    <option value="{{ $upazila->id }}">{{ $upazila->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Status" name="status" col="col-md-4" class="selectpicker">
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </x-form.selectbox>
                        <div class="col-md-4">
                            <div style="margin-top:28px;">     
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        @if (permission('sr-bulk-delete'))
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        @endif
                                        <th>Sl</th>
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Warehouse</th>
                                        <th>ASM</th>
                                        <th>District</th>
                                        <th>Upazila</th>
                                        <th>Status</th>
                                        <th>Balance</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('salesmen::view')
@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script>
var table;
$(document).ready(function(){

    table = $('#dataTable').DataTable({
        "processing": true, //Feature control the processing indicator
        "serverSide": true, //Feature control DataTable server side processing mode
        "order": [], //Initial no order
        "responsive": true, //Make table responsive in mobile device
        "bInfo": true, //TO show the total number of data
        "bFilter": false, //For datatable default search box show/hide
        "lengthMenu": [
            [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
            [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
        ],
        "pageLength": 25, //number of data show per page
        "language": { 
            processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
            emptyTable: '<strong class="text-danger">No Data Found</strong>',
            infoEmpty: '',
            zeroRecords: '<strong class="text-danger">No Data Found</strong>'
        },
        "ajax": {
            "url": "{{route('sales.representative.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.name     = $("#form-filter #name").val();
                data.phone    = $("#form-filter #phone").val();
                data.warehouse_id    = $("#form-filter #warehouse_id").val();
                data.asm_id    = $("#form-filter #asm_id").val();
                data.district_id    = $("#form-filter #district_id").val();
                data.upazila_id    = $("#form-filter #upazila_id").val();
                data.status   = $("#form-filter #status").val();
                data._token   = _token;
            }
        },
        "columnDefs": [{
            @if (permission('sr-bulk-delete'))
            "targets": [0,11],
            @else
            "targets": [10],
            @endif
                
                "orderable": false,
                "className": "text-center"
            },
            {
                @if (permission('sr-bulk-delete'))
                "targets": [1,2,4,5,7,8,9],
                @else
                "targets": [0,1,3,4,6,7,8],
                @endif
                "className": "text-center"
            },
            {
                @if (permission('sr-bulk-delete'))
                "targets": [10],
                @else
                "targets": [9],
                @endif
                "className": "text-right"
            }
        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            {
                'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
            },
            {
                "extend": 'print',
                'text':'Print',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "orientation": "landscape", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    @if (permission('sr-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                    @else
                        columns: ':visible:not(:eq(10))' 
                    @endif
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                },
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    @if (permission('sr-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                    @else
                        columns: ':visible:not(:eq(10))' 
                    @endif
                }
            },
            {
                "extend": 'excel',
                'text':'Excel',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    @if (permission('sr-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                    @else
                        columns: ':visible:not(:eq(10))' 
                    @endif
                }
            },
            {
                "extend": 'pdf',
                'text':'PDF',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "orientation": "landscape", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    @if (permission('sr-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                    @else
                        columns: ':visible:not(:eq(10))' 
                    @endif
                },
            },
            
            @if (permission('sr-bulk-delete'))
            {
                'className':'btn btn-danger btn-sm delete_btn d-none text-white',
                'text':'Delete',
                action:function(e,dt,node,config){
                    multi_delete();
                }
            }
            @endif
        ],
    });

    $('#btn-filter').click(function () {
        table.ajax.reload();
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('#form-filter .selectpicker').selectpicker('refresh');
        table.ajax.reload();
    });

    $(document).on('click', '.view_data', function () {
        let id = $(this).data('id');
        if (id) {
            $.ajax({
                url: "{{route('sales.representative.view')}}",
                type: "POST",
                data: { id: id,_token: _token},
                success: function (data) {
                    $('#view_modal #view-data').html('');
                    $('#view_modal #view-data').html(data);
                    $('#view_modal').modal({
                        keyboard: false,
                        backdrop: 'static',
                    });
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    });

    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('sales.representative.delete') }}";
        delete_data(id, url, table, row, name);
    });

    function multi_delete(){
        let ids = [];
        let rows;
        $('.select_data:checked').each(function(){
            ids.push($(this).val());
            rows = table.rows($('.select_data:checked').parents('tr'));
        });
        if(ids.length == 0){
            Swal.fire({
                type:'error',
                title:'Error',
                text:'Please checked at least one row of table!',
                icon: 'warning',
            });
        }else{
            let url = "{{route('sales.representative.bulk.delete')}}";
            bulk_delete(ids,url,table,rows);
        }
    }

    $(document).on('click', '.change_status', function () {
        let id     = $(this).data('id');
        let name   = $(this).data('name');
        let status = $(this).data('status');
        let row    = table.row($(this).parent('tr'));
        let url    = "{{ route('sales.representative.change.status') }}";
        change_status(id, url, table, row, name, status);
    });



});


</script>
@endpush