@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
     .small-btn{
        width: 25px !important;
        height: 25px !important;
        padding: 0 !important;
    }
    .small-btn i{font-size: 10px !important;} 
</style>
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
                    <a href="{{ route('damage.product') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form id="sale_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="customer_id" value="{{ $sale->customer_id }}">

                            <div class="form-group col-md-3 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $sale->memo_no }}"  readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="sale_date">Sold Date</label>
                                <input type="text" class="form-control" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="damage_date">Damage Date</label>
                                <input type="text" class="form-control date" name="damage_date" id="damage_date" value="{{ date('Y-m-d') }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Warehouse</label>
                                <input type="text" class="form-control" value="{{ $sale->warehouse->name }}" readonly />
                                <input type="hidden" class="form-control" name="warehouse_id" value="{{ $sale->warehouse_id }}" />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Order Received By</label>
                                <input type="text" class="form-control" value="{{ $sale->salesmen->name }}" readonly />
                                <input type="hidden" class="form-control" name="salesmen_id" value="{{ $sale->salesmen_id }}" />
                                <input type="hidden" class="form-control" name="sr_commission_rate" value="{{ $sale->sr_commission_rate }}" />
                                <input type="hidden" class="form-control" name="total_commission" value="{{ $sale->total_commission }}" />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Route</label>
                                <input type="text" class="form-control" value="{{ $sale->route->name }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Area</label>
                                <input type="text" class="form-control" value="{{ $sale->area->name }}" readonly />
                            </div>

                            <div class="form-group col-md-3 required">
                                <label for="customer_name">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ $sale->customer->shop_name.' - '.$sale->customer->name }}" readonly />
                            </div>
                            

                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Sold Qty</th>
                                        <th class="text-center">Damage Qty</th>
                                        <th class="text-right">Net Unit Price</th>
                                        <th class="text-right">Subtotal</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <!-- @if (!$sale->sale_products->isEmpty())
                                            @foreach ($sale->sale_products as $key => $sale_product)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$sale_product->pivot->tax_rate)->first();

                                                        $unit_name = DB::table('units')->where('id',$sale_product->pivot->sale_unit_id)->value('unit_name');
                                                        
                                                        $total_damage_qty = \DB::table('damage_products')
                                                        ->where([
                                                            ['memo_no',$sale->memo_no],
                                                            ['product_id',$sale_product->pivot->product_id],
                                                            ['batch_no', $sale_product->pivot->batch_no]
                                                        ])
                                                        ->sum('damage_qty');
                                                        $sold_qty = $sale_product->pivot->qty - $total_damage_qty;

      
                                                    @endphp
                                                    <td>{{ $sale_product->name }}</td>
                                                    <td class="text-center">{{ $sale_product->code }}</td>
                                                    <td class="unit-name text-center">{{ $unit_name }}</td>
                                                    <td><input type="text" class="sold_qty_{{ $key+1 }} form-control text-center" name="products[{{ $key+1 }}][sold_qty]"  value="{{ $sold_qty }}" readonly></td>
                                                    <td><input type="text" class="form-control damage_qty_{{ $key+1 }} text-center" onkeyup="quantity_calculate('{{ $key+1 }}')" onchange="quantity_calculate('{{ $key+1 }}')" name="products[{{ $key+1 }}][damage_qty]" id="products_{{ $key+1 }}_damage_qty" placeholder="0"></td>
                                                    <td><input type="text" class="net_unit_price_{{ $key+1 }} form-control text-right" name="products[{{ $key+1 }}][net_unit_price]" value="{{ $sale_product->pivot->net_unit_price }}"></td>
                                                    <td class="sub-total sub-total-{{ $key+1 }} text-right"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-md remove-product small-btn"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]"  value="{{ $sale_product->pivot->product_id }}">
                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]" value="{{ $sale_product->code }}">
                                                    <input type="hidden" class="product-batch" name="products[{{ $key+1 }}][batch_no]" value="{{ $sale_product->pivot->batch_no }}">
                                                    <input type="hidden" class="sale-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="subtotal subtotal_{{ $key+1 }}" name="products[{{ $key+1 }}][total]" >
                                
                                                </tr>
                                            @endforeach
                                        @endif -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" rowspan="3">
                                                <label  for="reason">Reason</label>
                                                <textarea class="form-control" name="reason" id="reason"></textarea><br>
                                            </td>
                                        </tr>
                                        <tr class="d-none">
                                            <td class="text-right" colspan="1" ><b>Total Tax</b></td>
                                            <td class="text-right">
                                                <input type="hidden" name="total_price" id="total_price">
                                                <input type="hidden" name="tax_rate" id="tax_rate" value="{{ $sale->order_tax_rate ? $sale->order_tax_rate : 0 }}">
                                                <input id="total_tax_ammount" tabindex="-1" class="form-control text-right valid" name="total_tax" placeholder="0.00" readonly="readonly" aria-invalid="false" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"  class="text-right"><b>Net Damage</b></td>
                                            <td class="text-right">
                                                <input type="text" id="grandTotal" class="form-control text-right" name="grand_total_price" placeholder="0.00" readonly="readonly" />
                                            </td>
                                            <td class="text-center"><button type="button" class="btn btn-success btn-sm add-product"><i class="fas fa-plus"></i></button></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="form-grou col-md-12 text-right pt-5">
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="save_data()">Damage</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
    $("input,select,textarea").bind("keydown", function (e) {
        var keyCode = e.keyCode || e.which;
        if(keyCode == 13) {
            e.preventDefault();
            $('input, select, textarea')
            [$('input,select,textarea').index(this)+1].focus();
        }
    });
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    $('#save-btn').prop("disabled", true);
    // $('input:checkbox').click(function () {
    //     if ($(this).is(':checked')) {
    //         $('#save-btn').prop("disabled", false);
    //     } else {
    //         if ($('.chk').filter(':checked').length < 1) {
    //             $('#save-btn').attr('disabled', true);
    //         }
    //     }
    // }); 
    @if (!$sale->sale_products->isEmpty())
    var count = "{{ count($sale->sale_products) + 1 }}" ;
    @else 
    var count = 1;
    @endif
    $('#product_table').on('click','.add-product',function(){
        count++;
        product_row_add(count);
        
        if($('#product_table tbody tr').length >= 1){
            $('#save-btn').prop("disabled", false);
        }else{
            $('#save-btn').attr('disabled', true);
        }
    });    
    function product_row_add(count){
        var newRow = $('<tr>');
        var cols = '';
        cols += `<td><select name="products[${count}][pro_id]" id="product_list_${count}" class="fcs col-md-12  products-alls product_details_${count} form-control" onchange="getProductDetails(this,${count},{{ $sale->id }})" data-live-search="true" data-row="${count}">
            @if (!$products->isEmpty())
            <option value="0">Please Select</option>
            @foreach ($products as $product)
                <option value="{{ $product->product_id }}"  data-pro_code="{{ $product->code}}" data-pro_avl_qty="{{ $product->qty}}" data-pro_net_price="{{ $product->price}}" data-pro_net_tax_rate="{{ $product->tax_rate}}" data-pro_unit="{{ $product->unit_name}}" >{{ $product->name }}</option>
            @endforeach
            @endif
        </select></td>`;
        cols += `<td class="product-code_tx_${count} text-center" id="products_code_${count}" data-row="${count}"></td>`
        cols += `<td class="unit-name_tx_${count} text-center" id="products_unit_${count}" data-row="${count}"></td>`;
        cols += `<td><input type="text" class="fcs form-control sold_qty_${count} text-center" name="products[${count}][sold_qty]" id="sold_qty_${count}" value="0" data-row="${count}" readonly></td>`;
        cols += `<td><input type="text" class="fcs form-control damage_qty_${count} text-center" name="products[${count}][damage_qty]" id="products_${count}_damage_qty" onkeyup="quantity_calculate('${count}')" onchange="quantity_calculate('${count}')" value="0" data-row="${count}"></td>`;
        cols += `<td><input type="text" class="fcs text-right form-control net_unit_price net_unit_price_${count}" name="products[${count}][net_unit_price]" id="products_net_unit_price_${count}" data-row="${count}"></td>`;
        cols += `<td class="sub-total text-right sub-total-${count}" id="sub_total_tx_${count}" data-row="${count}"></td>`;
        cols += `<td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-sm remove-product"><i class="fas fa-trash"></i></button></td>`;
        cols += `<input type="hidden" class="product-id_vl_${count}" name="products[${count}][id]" id="products_id_vl_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="product-code_vl_${count}" name="products[${count}][code]" id="products_code_vl_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="product-batch_vl_${count}" name="products[${count}][batch_no]" id="products_batch_no_${count}"  data-row="${count}">`;
        cols += `<input type="hidden" class="sale-unit_vl_${count}" name="products[${count}][unit]" id="sale_unit_${count}"  data-row="${count}">`;
        cols += `<input type="hidden" class="subtotal subtotal_${count}" name="products[${count}][total]" id="subtotal_value_vl_${count}" data-row="${count}">`;
        newRow.append(cols);
        $('#product_table tbody').append(newRow);
        $('#product_table .selectpicker').selectpicker();
    }

    //Remove product from cart table
    $('#product_table').on('click','.remove-product',function(){
        rowindex = $(this).closest('tr').index();
        $(this).closest('tr').remove();
        net_damage_calculation();
        if($('#product_table tbody tr').length >= 1){
            $('#save-btn').prop("disabled", false);
        }else{
            $('#save-btn').attr('disabled', true);
        }
    });
});
function getProductDetails(data,row,sale_id) {
        var rowindex = $('#product_list_'+row).closest('tr').index();
        var temp_data = $(data).val();   
        $.ajax({
            url: '{{ route("damage.product.search.with.id.for.damage") }}',
            type: 'POST',
            data: {
                data: temp_data,sale_id: sale_id,_token:_token
            },
            success: function(data) {
                temp_unit_name = data.unit_name.split(',');
                $('#products_code_'+row).text(data.code);
                $('#products_unit_'+row).text(temp_unit_name[0]);
                $('#sold_qty_'+row).val(data.qty);
                $('#products_net_unit_price_'+row).val(data.price);
                $('#tax_tx_'+row).text(data.tax_name);
                $('#products_id_vl_'+row).val(data.id);
                $('#products_code_vl_'+row).val(data.code);
                $('#products_batch_no_'+row).val(data.batch_no);
                $('#sale_unit_'+row).val(temp_unit_name[0]);
            }
        });
    }

function setReturnValue(row)
{
    $('#products_'+row+'_return_checkbox').is(':checked') ? $('#return_'+row).val(1) : $('#return_'+row).val(2); 
}

function quantity_calculate(row) {
    
    var sold_qty = $(".sold_qty_" + row).val();
    var damage_qty = $(".damage_qty_" + row).val();
    var price_item = $(".net_unit_price_" + row).val();
    if(parseFloat(sold_qty) < parseFloat(damage_qty)){
        $(".damage_qty_"+row).val("0");
        notification('error','Sold quantity less than quantity!');
    }
    if (parseFloat(damage_qty) > 0) {
        var price = (damage_qty * price_item);
        var deduction_amount = 0;

        //Total price calculate per product
        var temp = price - deduction_amount;
        $(".subtotal_" + row).val(temp);
        $(".sub-total-" + row).text(parseFloat(temp).toFixed(2));
        net_damage_calculation();
        
    }

}

function net_damage_calculation()
{
    var a = 0,o = 0,d = 0,p = 0;
    $(".subtotal").each(function () {
        isNaN(this.value) || o == this.value.length || (a += parseFloat(this.value));
    });
    var tax_rate = parseFloat($('#tax_rate').val());
    var total_tax_ammount = a * (tax_rate/100);
    var grand_total = a + total_tax_ammount;
    $("#total_price").val(a.toFixed(2, 2));
    $("#total_tax_ammount").val(total_tax_ammount.toFixed(2, 2));
    $("#grandTotal").val(grand_total.toFixed(2, 2));
}

function save_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to return table!")
    }else{
        let form = document.getElementById('sale_update_form');
        let formData = new FormData(form);
        let url = "{{route('damage.product.store')}}";
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
                $('#sale_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#sale_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#sale_update_form input#' + key).addClass('is-invalid');
                        $('#sale_update_form textarea#' + key).addClass('is-invalid');
                        $('#sale_update_form select#' + key).parent().addClass('is-invalid');
                        $('#sale_update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('damage.product') }}");
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