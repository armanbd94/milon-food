

@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
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
                    @if (permission('transfer-inventory-add'))
                    <a href="{{ route('transfer.inventory.create') }}"  class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-plus-circle"></i> Add New</a>
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
                        <x-form.textbox labelName="Challan No." name="challan_no" col="col-md-3" />
                       
                        <div class="form-group col-md-3">
                            <label for="name">Choose Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed">
                                <input type="hidden" id="start_date" name="start_date">
                                <input type="hidden" id="end_date" name="end_date">
                            </div>
                        </div>
                        @if(empty(Auth::user()->warehouse_id))
                        <x-form.selectbox labelName="From Warehouse" name="from_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="To Warehouse" name="to_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Transfer Status" name="transfer_status" col="col-md-3" class="selectpicker">
                                <option value="1">Transfered</option>
                                <option value="2">Pending</option>
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Receive Status" name="receive_status" col="col-md-3" class="selectpicker">
                            @foreach (TRANSFER_RECEIVE_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        @endif
                        

                        <div class="{{ empty(Auth::user()->warehouse_id) ? 'col-md-6' : 'col-md-6' }}">
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
                                        @if (permission('transfer-inventory-bulk-delete'))
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        @endif
                                        <th>Sl</th>
                                        <th>Transfer No.</th>
                                        <th>Transfer Date</th>
                                        @if(empty(Auth::user()->warehouse_id))
                                        <th>From Warehouse</th>
                                        <th>To Warehouse</th>
                                        @endif
                                        <th>Total Item</th>
                                        @if(empty(Auth::user()->warehouse_id))
                                        <th>Stock Value</th>
                                        <th>Transfer Status</th>
                                        <th>Receive Info.</th>
                                        <th>Created By</th>
                                        @else
                                        <th>Receive Info.</th>
                                        @endif
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
@include('transfer::receive-modal')
@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
var table;
$("#kt_body").addClass("aside-minimize");
$(document).ready(function(){
    
    $('.daterangepicker-filed').daterangepicker({
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date   = endDate.format('YYYY-MM-DD');
            var title      = start_date + ' To ' + end_date;
            $(this).val(title);
            $('input[name="start_date"]').val(start_date);
            $('input[name="end_date"]').val(end_date);
        }
    });
    
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
            "url": "{{route('transfer.inventory.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.transfer_no       = $("#form-filter #transfer_no").val();
                data.from_warehouse_id = $("#form-filter #from_warehouse_id").val();
                data.to_warehouse_id   = $("#form-filter #to_warehouse_id").val();
                data.from_date         = $("#form-filter #start_date").val();
                data.to_date           = $("#form-filter #end_date").val();
                data.transfer_status   = $("#form-filter #transfer_status").val();
                data.receive_status    = $("#form-filter #receive_status").val();
                data._token            = _token;
            }
        },
        "columnDefs": [{
                @if (permission('transfer-inventory-bulk-delete'))
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [0,11],
                    @else
                    "targets": [0,6],
                    @endif
                @else
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [10],
                    @else
                    "targets": [5],
                    @endif
                @endif
                "orderable": false,
                "className": "text-center"
            },
            {
                @if (permission('transfer-inventory-bulk-delete'))
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [1,2,3,4,5,6,8,10],
                    @else
                    "targets": [1,2,3,4],
                    @endif
                @else
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [0,1,2,3,4,5,7,9],
                    @else
                    "targets": [0,1,2,3],
                    @endif
                @endif
                "className": "text-center"
            },
            {
                @if (permission('transfer-inventory-bulk-delete'))
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [7],
                    @endif
                @else
                    @if(empty(Auth::user()->warehouse_id))
                    "targets": [6],
                    @endif
                @endif
                "className": "text-right"
            },

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
                    @if (permission('transfer-inventory-bulk-delete'))
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                        @else
                        columns: ':visible:not(:eq(0),:eq(6))' 
                        @endif
                    @else
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(10))' 
                        @else
                        columns: ':visible:not(:eq(5))' 
                        @endif
                    @endif
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                    $(win.document.body).find('table thead').css({'background':'#034d97'});
                    $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('font-size', '15px');
                    $(win.document.body).find('table').css( 'font-size', 'inherit' );
                },
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    @if (permission('transfer-inventory-bulk-delete'))
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                        @else
                        columns: ':visible:not(:eq(0),:eq(6))' 
                        @endif
                    @else
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(10))' 
                        @else
                        columns: ':visible:not(:eq(5))' 
                        @endif
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
                    @if (permission('transfer-inventory-bulk-delete'))
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                        @else
                        columns: ':visible:not(:eq(0),:eq(6))' 
                        @endif
                    @else
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(10))' 
                        @else
                        columns: ':visible:not(:eq(5))' 
                        @endif
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
                    @if (permission('transfer-inventory-bulk-delete'))
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(0),:eq(11))' 
                        @else
                        columns: ':visible:not(:eq(0),:eq(6))' 
                        @endif
                    @else
                        @if(empty(Auth::user()->warehouse_id))
                        columns: ':visible:not(:eq(10))' 
                        @else
                        columns: ':visible:not(:eq(5))' 
                        @endif
                    @endif
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                    doc.styles.tableHeader.fontSize = 7;
                    doc.pageMargins = [5,5,5,5];
                }  
            },
            @if (permission('transfer-inventory-bulk-delete'))
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
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('transfer.inventory.delete') }}";
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
            let url = "{{route('transfer.inventory.bulk.delete')}}";
            bulk_delete(ids,url,table,rows);
        }
    }

    $(document).on('click', '.receive_data', function () {
        let id = $(this).data('id');
        if (id) {
            $.ajax({
                url: "{{url('transfer-inventory/data')}}/"+id,
                type: "GET",
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

});
function calculateQty(row)
{
    const transfer_qty = $(`#products_${row}_transfer_qty`).val() ? parseFloat($(`#products_${row}_transfer_qty`).val()) : 0;
    const receive_qty = parseFloat($(`#products_${row}_receive_qty`).val());
    const cost = $(`#products_${row}_net_unit_cost`).val() ? parseFloat($(`#products_${row}_net_unit_cost`).val()) : 0;
    const damage_qty  = transfer_qty - receive_qty;
    if(receive_qty < 0 && receive_qty != '')
    {
        $(`#products_${row}_receive_qty,#products_${row}_damage_qty`).val(0);
        notification('error','Receive quantity must be grater than or equal to 0!');
    }else{
        if(isNaN(receive_qty)){
            $(`#products_${row}_receive_qty,#products_${row}_damage_qty`).val('');
        }else{
            if (receive_qty > transfer_qty) {
                $(`#products_${row}_receive_qty,#products_${row}_damage_qty`).val(0);
                $(`#products_${row}_damage_cost`).val(0);
                notification('error','Receive quantity must be leass than or equal to transfered quantity!');
            }else{
                $(`#products_${row}_damage_qty`).val(parseFloat(damage_qty));
                $(`#products_${row}_damage_cost`).val(parseFloat(cost * damage_qty));
            }
            calculateTotal();
        }
        calculateTotal();
    } 
    
    
}
function calculateTotal()
{
    let receive_qty = 0;
    let damage_qty = 0;
    let damage_cost = 0;
    $('.receive_qty').each(function() {
        if($(this).val() == ''){
            receive_qty += 0;
        }else{
            receive_qty += parseFloat($(this).val());
        }
    });
    $('.damage_qty').each(function() {
        if($(this).val() == ''){
            damage_qty += 0;
        }else{
            damage_qty += parseFloat($(this).val());
        }
    });
    $('.damage_cost').each(function() {
        if($(this).val() == ''){
            damage_cost += 0;
        }else{
            damage_cost += parseFloat($(this).val());
        }
    });
    $('#receive-qty').text(receive_qty);
    $('#receive_qty').val(receive_qty);
    $('#damage-qty').text(damage_qty);
    $('#damage_qty').val(damage_qty);
    $('#total_damage_cost').val(damage_cost);
}
function store_data(){
    var receive_qty = $('#receive_form #receive_qty').val() ? parseFloat($('#receive_form #receive_qty').val()) : 0;
    if (receive_qty <= 0) {
        notification("error","Please insert product receive qty!")
    }else{
        let form = document.getElementById('receive_form');
        let formData = new FormData(form);
        let url = "{{route('transfer.inventory.receive')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#save-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#save-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#receive_form').find('.is-invalid').removeClass('is-invalid');
                $('#receive_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#receive_form input#' + key).addClass('is-invalid');
                        $('#receive_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        table.ajax.reload(null, false);
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
    
}
</script>
@endpush