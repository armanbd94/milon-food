@extends('layouts.app')

@section('title', $page_title)


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
                    @if ($production->production_status != 3)
                    <button type="button" class="btn btn-primary btn-sm mr-5" onclick="store_data()" id="save-btn"><i class="fas fa-save"></i> Save</button>
                    <button type="button" class="btn btn-primary btn-sm mr-5 change_production_status"  data-id="{{ $production->id }}" 
                        data-name="{{ $production->batch_no }}" data-status="{{ $production->production_status }}">
                        <i class="fas fa-check-circle text-white mr-2"></i> Change Production Status</button>
                    @endif
                    
                    <a href="{{ route('production') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 text-center">
                        <h5>
                            <b>Batch No.:</b> {{ $production->batch_no }} <br>
                            <b>Warehouse:</b> {{ $production->warehouse->name }} <br>
                            <b>Start Date:</b> {{ date('d-M-Y',strtotime($production->start_date)) }}
                        </h5>
                    </div>
                    <div class="col-md-12 pt-5">
                        @if (!$production->products->isEmpty())
                            @foreach ($production->products as $key => $item)
                            <div class="row pt-5">
                                <div class="col-md-12 text-center">
                                    <h3 class="py-3 bg-warning text-white" style="margin: 10px auto 10px auto;">{{ ($key+1).' - '.$item->product->name }}</h3>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered pb-5" id="material_table_{{ $key + 1 }}">
                                        <thead class="bg-primary">
                                            <th class="text-center">Mfg. Date</th>
                                            <th class="text-center">Exp. Date</th>
                                            <th class="text-center">Unit Name</th>
                                            <th class="text-center">Finish Goods Qty</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">{{ date('d-M-Y',strtotime($item->mfg_date)) }}</td>
                                                <td class="text-center">{{ date('d-M-Y',strtotime($item->exp_date)) }}</td>
                                                <td class="text-center">{{ $item->product->base_unit->unit_name.' ('.$item->product->base_unit->unit_code.')' }}</td>
                                                <td>
                                                    <input type="text" class="form-control text-center" value="{{ $item->base_unit_qty }}" name="production[{{ $key+1 }}][fg_qty]" id="production_{{ $key+1 }}_fg_qty" onkeyup="per_unit_cost('{{ $key+1 }}')">
                                                    <input type="hidden" class="form-control" name="production[{{ $key+1 }}][production_product_id]" value="{{ $item->id }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-bordered pb-5" id="material_table_{{ $key + 1 }}">
                                        <thead class="bg-primary">
                                            <th width="30%">Material</th>
                                            <th width="5%" class="text-center">Type</th>
                                            <th width="10%" class="text-center">Unit Name</th>
                                            <th width="10%" class="text-right">Rate</th>
                                            <th class="text-center">Received Qty</th>
                                            <th class="text-center">Used Qty</th>
                                            <th class="text-center">Damaged Qty</th>
                                            <th class="text-center">Odd Qty</th>
                                        </thead>
                                        <tbody>
                                            @if (!$item->materials->isEmpty())
                                                @foreach ($item->materials as $index => $value)
                                                    <tr>
                                                        <td>
                                                            {{ $value->material_name .' ('.$value->material_code.')' }}
                                                            <input type="hidden" class="form-control text-center" value="{{ $value->pivot->id }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][production_material_id]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_production_material_id" data-id="{{ $index+1 }}">
                                                        </td>
                                                        <td class="text-center">{{ MATERIAL_TYPE[$value->type] }}</td>
                                                        <td class="text-center">{{ $value->unit->unit_name.' ('.$value->unit->unit_code.')' }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($value->pivot->cost,2,'.','') }}
                                                            <input type="hidden" class="form-control text-right material_{{ $key+1 }}_cost" value="{{ $value->pivot->cost }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][cost]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_cost" data-id="{{ $index+1 }}">
                                                        </td>
                                                        <td class="text-center">
                                                            {{ number_format($value->pivot->qty,2,'.','') }}
                                                            <input type="hidden" class="form-control text-right material_{{ $key+1 }}_qty" value="{{ $value->pivot->qty }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][qty]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_qty" data-id="{{ $index+1 }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control text-center material_{{ $key+1 }}_used_qty" value="{{ $value->pivot->used_qty ?? 0 }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][used_qty]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_used_qty"  onkeyup="calculateRowData('{{ $key+1 }}','{{ $index+1 }}')" data-id="{{ $index+1 }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control text-center material_{{ $key+1 }}_damaged_qty" value="{{ $value->pivot->damaged_qty ?? 0 }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][damaged_qty]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_damaged_qty"  onkeyup="calculateRowData('{{ $key+1 }}','{{ $index+1 }}')" data-id="{{ $index+1 }}">
                                                        </td>
                                                        <td>
                                                            <input readonly type="text" class="form-control bg-secondary text-center material_{{ $key+1 }}_odd_qty" value="{{ $value->pivot->odd_qty ?? 0 }}" name="production[{{ $key+1 }}][materials][{{ $index+1 }}][odd_qty]" id="production_{{ $key+1 }}_materials_{{ $index+1 }}_odd_qty"  data-id="{{ $index+1 }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="6" class="text-right font-weight-bold">Total Cost</td>
                                                    <td colspan="2">
                                                        @php
                                                            if(!empty($item->per_unit_cost) && !empty($item->base_unit_qty))
                                                            {
                                                                $total_cost = $item->per_unit_cost * $item->base_unit_qty;
                                                            }else{
                                                                $total_cost = '';
                                                            }
                                                        @endphp
                                                        <input readonly type="text" class="form-control text-white bg-primary text-right material_{{ $key+1 }}_total_cost" value="{{ $total_cost }}" name="production[{{ $key+1 }}][materials_total_cost]" id="production_{{ $key+1 }}_materials_total_cost"  data-id="{{ $key+1 }}">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right font-weight-bold">Per Unit Cost</td>
                                                    <td colspan="2">
                                                        <input readonly type="text" class="form-control text-white bg-primary text-right material_{{ $key+1 }}_per_unit_cost" value="{{ $item->per_unit_cost ? number_format($item->per_unit_cost,2,'.','') : '' }}" name="production[{{ $key+1 }}][materials_per_unit_cost]" id="production_{{ $key+1 }}_materials_per_unit_cost"  data-id="{{ $key+1 }}">
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        @endif
                        
                    </div>
                </div>
            </form>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('production::production.production-status-modal')
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    $(document).on('click','#print-qrcode',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#printableArea").printArea(options);
    });

    $(document).on('click','.change_production_status',function(){
        $('#production_status_form #production_id').val($(this).data('id'));
        $('#production_status_form #production_status').val($(this).data('status'));
        $('#production_status_form #production_status.selectpicker').selectpicker('refresh');
        $('#production_status_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#production_status_modal .modal-title').html('<span>Change Production Status</span>');
        $('#production_status_modal #production-status-btn').text('Change Status');
            
    });

    $(document).on('click','#production-status-btn',function(){
        var production_id     = $('#production_status_form #production_id').val();
        var production_status =  $('#production_status_form #production_status option:selected').val();
        if(production_id && production_status)
        {
            $.ajax({
                url: "{{route('production.change.production.status')}}",
                type: "POST",
                data: {production_id:production_id,production_status:production_status,_token:_token},
                dataType: "JSON",
                beforeSend: function(){
                    $('#production-status-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#production-status-btn').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        $('#production_status_modal').modal('hide');
                        window.location.replace("{{ url('production') }}");
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }else{
            notification('error','Please select status');
        }
    });

});

function calculateRowData(key,index)
{
   
    var cost          = parseFloat($('#production_'+key+'_materials_'+index+'_cost').val());
    var qty           = parseFloat($('#production_'+key+'_materials_'+index+'_qty').val());
    var used_qty      = parseFloat($('#production_'+key+'_materials_'+index+'_used_qty').val());
    var damaged_qty   = parseFloat($('#production_'+key+'_materials_'+index+'_damaged_qty').val());
    
    used_qty    = used_qty ? used_qty : 0;
    damaged_qty = damaged_qty ? damaged_qty : 0;
    var odd_qty     = parseFloat(qty - (used_qty + damaged_qty));

    console.log('cost='+cost,'qty='+qty,'used='+used_qty,'damaged='+damaged_qty,'odd='+odd_qty);

    $('#production_'+key+'_materials_'+index+'_odd_qty').val(odd_qty.toFixed(2));
    per_unit_cost(key);
    
}

function per_unit_cost(key)
{
    var fg_qty        = parseFloat($('#production_'+key+'_fg_qty').val());
    fg_qty      = fg_qty ? fg_qty : 0;
    var total_cost    = 0;
    var per_unit_cost = 0;
    var total_used_qty_cost = 0;
    $('#material_table_'+key+' .material_'+key+'_used_qty').each(function() {
        var row = $(this).data('id');
        if($(this).val() == ''){
            total_used_qty_cost += 0;
        }else{
            total_used_qty_cost += parseFloat($(this).val()) * parseFloat($('#production_'+key+'_materials_'+row+'_cost').val());
        }
    });

    var total_damaged_qty_cost = 0;
    $('#material_table_'+key+' .material_'+key+'_damaged_qty').each(function() {
        var row = $(this).data('id');
        if($(this).val() == ''){
            total_damaged_qty_cost += 0;
        }else{
            total_damaged_qty_cost += parseFloat($(this).val()) * parseFloat($('#production_'+key+'_materials_'+row+'_cost').val());
        }
    });

    total_cost = total_used_qty_cost + total_damaged_qty_cost;
    $('.material_'+key+'_total_cost').val(parseFloat(total_cost).toFixed(2));

    if(fg_qty > 0)
    {
        var per_unit_cost = total_cost / fg_qty;
        $('.material_'+key+'_per_unit_cost').val(parseFloat(per_unit_cost).toFixed(2));
    }else{
        $('.material_'+key+'_per_unit_cost').val('');
    }
}

function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('production/store-operation')}}";
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
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value){
                    var key = key.split('.').join('_');
                    $('#store_or_update_form input#' + key).addClass('is-invalid');
                    $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                    $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                    $('#store_or_update_form #' + key).parent().append(
                    '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('production/operation',$production->id) }}");
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });

    
}

/***********************
 ## Begin :: QR Code ##
************************/
function show_qrcode_modal(production_product_id,batch_no,product_name)
{
    $('#qrcode_form')[0].reset();
    $('#qrcode_form #production_product_id').val(production_product_id);
    $('#qrcode_form #batch_no').val(batch_no);
    $('#qrcode_form #row_qty').val(1);
    $('#qrcode_modal').modal({
        keyboard: false,
        backdrop: 'static',
    });
    $('#qrcode_modal .modal-title').html('Print QR Code for '+product_name);
    load_qrcode_view();
}

function load_qrcode_view()
{
    var production_product_id    = $('#qrcode_form #production_product_id').val();
    var batch_no    = $('#qrcode_form #batch_no').val();
    var row_qty     = $('#qrcode_form #row_qty').val();
    
    if(production_product_id && batch_no && row_qty){
        $.ajax({
            url: "{{ route('production.generate.coupon.qrcode') }}",
            type: "POST",
            data: {production_product_id:production_product_id,batch_no:batch_no,row_qty:row_qty,_token:_token},
            beforeSend: function(){
                $('#generate-qrcode').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#generate-qrcode').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#qrcode_form').find('.is-invalid').removeClass('is-invalid');
                $('#qrcode_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#qrcode_form input#' + key).addClass('is-invalid');
                        $('#qrcode_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                    });
                } 
                if(data) {
                    $('#qrcode-section').empty().html(data);
                }
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
}
/***********************
 ## End :: QR Code ##
************************/
</script>
@endpush