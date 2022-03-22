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
                    <button type="button" class="btn btn-primary btn-sm mr-5" onclick="store_data()" id="save-btn"><i class="fas fa-dolly-flatbed"></i> Transfer</button>
                   
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
                    <div class="col-md-12">
                        <div class="row">
                            <x-form.textbox labelName="Batch No." name="batch_no" value="{{ $production->batch_no }}" property="readonly" required="required" col="col-md-3"/>
                            <x-form.textbox labelName="Chalan No." name="chalan_no" value="{{ $production->chalan_no ? $production->chalan_no : 'KEAPLTR-'.$production->id }}" property="readonly" required="required" col="col-md-3"/>
                            <x-form.textbox labelName="Transfer Date" name="transfer_date" required="required" col="col-md-3" property="readonly" class="date" value="{{ $production->transfer_date ? $production->transfer_date : date('Y-m-d') }}"/>
                            <x-form.textbox labelName="Warehouse" name="warehouse" value="{{ $production->warehouse->name }}" property="readonly" required="required" col="col-md-3"/>
                        </div> 
                    </div>
                    <div class="col-md-12 pt-5">
                        <table class="table table-bordered pb-5" id="material_table">
                            <thead class="bg-primary">
                                <th>Name</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Base Unit</th>
                                <th class="text-center">Qty Unit</th>
                                <th class="text-center">Qty Base Unit</th>
                                <th class="text-right">Net Unit Cost</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Sub Total</th>
                            </thead>
                            <tbody>
                                @php 
                                    $total_cost = $total_unit_qty = $total_base_unit_qty = $total_tax = 0; 
                                @endphp
                                @if (!$production->products->isEmpty())
                                    @foreach ($production->products as $key => $item)
                                    @php
                                    if($item->product->unit->operator == '*')
                                    {
                                        $qty_unit = $item->base_unit_qty / $item->product->unit->operation_value;
                                    }else{
                                        $qty_unit = $item->base_unit_qty * $item->product->unit->operation_value;
                                    }

                                    if($item->product->tax_method == 1)
                                    {
                                        $tax = $item->product->base_unit_price * $item->base_unit_qty * ($item->product->tax->rate/100);
                                        $sub_total = ($item->product->base_unit_price * $item->base_unit_qty) + $tax;
                                    }else{
                                        $net_unit_price = (100 / (100 + $item->product->tax->rate)) * $item->product->base_unit_price;
                                        $tax = ($item->product->base_unit_price - $net_unit_price) * $item->base_unit_qty;
                                        $sub_total = $item->product->base_unit_price * $item->base_unit_qty;
                                    }

                                    // $total_cost += $qty_unit * $item->product->unit_price;
                                    // echo $sub_total.'=='.$total_cost;
                                    // exit;
                                    $total_cost += $sub_total;
                                    $total_tax += $tax;
                                    $total_unit_qty += $qty_unit;
                                    $total_base_unit_qty += $item->base_unit_qty;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-center">{{ $item->product->code }}</td>
                                        <td class="text-center">{{ $item->product->unit->unit_name.' ('.$item->product->unit->unit_code.')' }}</td>
                                        <td class="text-center">{{ $item->product->base_unit->unit_name.' ('.$item->product->base_unit->unit_code.')' }}</td>
                                        <td class="text-center">{{ number_format($qty_unit,2,'.','') }}</td>
                                        <td class="text-center">{{ number_format($item->base_unit_qty,2,'.','') }}</td>
                                        <td class="text-right">{{ number_format($item->product->unit_price,2,'.','') }}</td>
                                        <td class="text-right">{{ number_format(($tax),2,'.','') }}</td>
                                        <td class="text-right">{{ number_format(($sub_total),2,'.','') }}</td>

                                        <input type="hidden" name="products[{{ $key }}][product_id]" value="{{ $item->product->id }}">
                                        <input type="hidden" name="products[{{ $key }}][unit_qty]" value="{{ $qty_unit }}">
                                        <input type="hidden" name="products[{{ $key }}][base_unit_qty]" value="{{ $item->base_unit_qty }}">
                                        <input type="hidden" name="products[{{ $key }}][net_unit_price]" value="{{ $item->product->unit_price }}">
                                        <input type="hidden" name="products[{{ $key }}][base_unit_price]" value="{{ $item->product->base_unit_price }}">
                                        <input type="hidden" name="products[{{ $key }}][tax_rate]" value="{{ $item->product->tax->rate }}">
                                        <input type="hidden" name="products[{{ $key }}][tax]" value="{{ $tax }}">
                                        <input type="hidden" name="products[{{ $key }}][total]" value="{{ $sub_total }}">
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-primary text-white">
                                    <th colspan="8" class="text-right">Total</th>
                                    <th class="text-right">
                                        {{ number_format($total_cost,2,'.','') }}
                                        
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-md-12 pt-5">
                        <div class="row">
                            <x-form.selectbox labelName="Transfer Status" name="transfer_status" required="required"  col="col-md-2" class="selectpicker">
                                @foreach (TRANSFER_STATUS as $key => $value)
                                    <option value="{{ $key }}" {{ $key == 2 ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.textbox labelName="Shipping Cost" name="shipping_cost" value="{{ $production->shipping_cost }}" col="col-md-2"/>
                            <x-form.textbox labelName="Labor Cost" name="labor_cost" value="{{ $production->labor_cost }}" col="col-md-2"/>
                            <x-form.textbox labelName="Received By" name="received_by" value="{{ $production->received_by }}" required="required" col="col-md-3"/>
                            <x-form.textbox labelName="Carried By" name="carried_by" value="{{ $production->carried_by }}" required="required" col="col-md-3"/>
                            <x-form.textarea labelName="Remarks" name="remarks" value="{{ $production->remarks }}" required="required" col="col-md-12"/>
                        </div>
                    </div>
                    <div class="col-md-12 pt-5">
                        <table class="table table-bordered">
                            <thead class="bg-primary">
                                <th><strong>Items</strong><span class="float-right" id="item"> 
                                    @if (!$production->products->isEmpty()) 
                                    {{ count($production->products).'('.$total_unit_qty.')'.'('.$total_base_unit_qty.')' }}
                                    @endif
                                </span></th>
                                <th><strong>Total</strong><span class="float-right" id="subtotal">{{ number_format($total_cost,2,'.','') }}</span></th>
                                <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost"></span></th>
                                <th><strong>Labor Cost</strong><span class="float-right" id="labor_total_cost"></span></th>
                                <th><strong>Grand Total</strong><span class="float-right" id="grand_total">{{ number_format($total_cost,2,'.','') }}</span></th>
                            </thead>
                        </table>
                        <input type="hidden" name="production_id" id="production_id" value="{{ $production->id }}">
                        <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $production->warehouse_id }}">
                        <input type="hidden" name="total_item" id="total_item" value="{{ count($production->products) }}">
                        <input type="hidden" name="total_unit_qty" id="total_unit_qty" value="{{ $total_unit_qty }}">
                        <input type="hidden" name="total_base_unit_qty" id="total_base_unit_qty" value="{{ $total_base_unit_qty }}">
                        <input type="hidden" name="total_tax" id="total_tax" value="{{ $total_tax }}">
                        <input type="hidden" name="total_cost" id="total" value="{{ $total_cost }}">
                        <input type="hidden" name="grand_total">
                    </div>
                </div>
            </form>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    

    calculateGrandTotal();
    function calculateGrandTotal()
    {
        var subtotal       = parseFloat($('#total').val());
        var shipping_cost  = parseFloat($('#shipping_cost').val());
        var labor_cost     = parseFloat($('#labor_cost').val());

        if(!shipping_cost){
            shipping_cost = 0.00;
        }
        if(!labor_cost){
            labor_cost = 0.00;
        }
        var grand_total = (subtotal + shipping_cost + labor_cost);


        $('#subtotal').text(subtotal.toFixed(2));
        $('#shipping_total_cost').text(shipping_cost.toFixed(2));
        $('#labor_total_cost').text(labor_cost.toFixed(2));
        $('#grand_total').text(grand_total.toFixed(2));
        $('input[name="grand_total"]').val(grand_total.toFixed(2));
        
    }

    $('input[name="shipping_cost"]').on('input',function(){
        calculateGrandTotal();
    });
    $('input[name="labor_cost"]').on('input',function(){
        calculateGrandTotal();
    });
});

function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('transfer/store')}}";
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
                    window.location.replace("{{ url('transfer/view') }}/"+data.transfer_id);
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
</script>
@endpush