@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
                    <a href="{{ route('transfer.inventory') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <form action="" id="store_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="transfer_id" value="{{ isset($transfer) ? $transfer->id : '' }}">
                        <div class="row">
                            <div class="form-group col-md-3 required">
                                <label for="challan_no">Challan No.</label>
                                <input type="text" class="form-control" name="challan_no" id="challan_no" value="{{ isset($transfer) ? $transfer->challan_no :  $challan_no }}" readonly />
                            </div>
                            <x-form.textbox labelName="Transfer Date" name="transfer_date" value="{{ isset($transfer) ? $transfer->transfer_date : date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>
                            @if(Auth::user()->warehouse_id)
                            <input type="hidden" name="from_warehouse_id" id="from_warehouse_id" value="{{ Auth::user()->warehouse_id }}">
                            @else
                            <x-form.selectbox labelName="From Warehouse" name="from_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                              @if (!$warehouses->isEmpty())
                                  @foreach ($warehouses as $value)
                                  <option value="{{ $value->id }}" {{ isset($transfer) ? ($transfer->from_warehouse_id == $value->id ? 'selected' : 'disabled') : ''  }}>{{ $value->name }}</option>
                                  @endforeach
                              @endif
                            </x-form.selectbox>
                            @endif
                            <x-form.selectbox labelName="To Warehouse" name="to_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $value)
                                <option value="{{ $value->id }}" {{ isset($transfer) ? ($transfer->to_warehouse_id == $value->id ? 'selected' : '') : ''  }}>{{ $value->name }}</option>
                                @endforeach
                            @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-3">
                                <label for="driver_name">Driver Name</label>
                                <input type="text" class="form-control" name="driver_name" id="driver_name" value="{{ isset($transfer) ? $transfer->driver_name :  '' }}"  />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="dmobile_no">Driver Mobile No.</label>
                                <input type="text" class="form-control" name="dmobile_no" id="dmobile_no" value="{{ isset($transfer) ? $transfer->dmobile_no :  '' }}"  />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="shipping_cost">Shiping Cost</label>
                                <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ isset($transfer) ? $transfer->shipping_cost :  '' }}"  />
                            </div>
                            <x-form.selectbox labelName="Transfer Status" name="transfer_status" required="required" col="col-md-3" class="selectpicker">
                                <option value="1" {{ isset($transfer) ? ($transfer->transfer_status == 1 ? 'selected' : '') : ''  }}>Transfered</option>
                                <option value="2" {{ isset($transfer) ? ($transfer->transfer_status == 2 ? 'selected' : '') : ''  }}>Pending</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Received By" name="receiver_id" required="required" col="col-md-3" class="selectpicker">
                                @if (!$users->isEmpty())
                                    @foreach ($users as $value)
                                    <option value="{{ $value->id }}" {{ isset($transfer) ? ($transfer->receiver_id == $value->id ? 'selected' : '') : ''  }}>{{ $value->name.' ('.$value->role->role_name.')' }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Product</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Transfer Qty</th>
                                        <th class="text-center">Cost</th>
                                        <th class="text-center">Subtotal</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @if (isset($transfer))
                                            @if (!$transfer->hasManyProducts->isEmpty())
                                                @foreach ($transfer->hasManyProducts as $key => $item)
                                                    @php
                                                        $stock_qty = 0;
                                                        $warehouse_stock = DB::table('warehouse_product')->where([
                                                            'warehouse_id'=>$transfer->from_warehouse_id,'product_id'=>$item->product_id])->value('qty');
                                                        
                                                        if($transfer->transfer_status == 1)
                                                        {
                                                            $stock_qty = ($warehouse_stock ?? 0) + $item->transfer_qty;
                                                        }else{
                                                            $stock_qty = ($warehouse_stock ?? 0);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <select name="products[{{ $key+1 }}][id]" id="products_{{ $key+1 }}_id" class="form-control product_id selectpicker" onchange="setProductDetails({{ $key+1 }})"  data-live-search="true" data-row="1">
                                                                @if (!$products->isEmpty())
                                                                <option value="">Please Select</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : 'disabled' }}
                                                                        data-cost="{{ $product->cost }}" data-unitname="{{ $product->unit->unit_name}}" data-unitid="{{ $product->unit->id }}">
                                                                        {{ $product->code.' - '.$product->name }}
                                                                    </option>
                                                                @endforeach
                                                                @endif
                                                            </select>
                                                        </td>
                                                        <td class="unit-name-{{ $key+1 }} text-center">{{ $item->unit->unit_name }}</td>
                                                        <td class="stock-qty-{{ $key+1 }} text-center">{{ $stock_qty }}</td>
                                                        <td>
                                                            <input type="text" class="form-control transfer_qty text-center" 
                                                            value="{{ $item->transfer_qty }}" name="products[{{ $key+1 }}][transfer_qty]" 
                                                            id="products_{{ $key+1 }}_transfer_qty">
                                                        </td>
                                                        <td class="net-unit-cost-{{ $key+1 }} text-center">{{ number_format($item->net_unit_cost,2,'.','') }}</td>
                                                        <td class="sub-total-{{ $key+1 }} subtotal text-center">{{ number_format($item->total,2,'.','') }}</td>
                                                        <td class="text-center">
                                                            @if($key != 0 )
                                                            <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button>
                                                            @endif
                                                        </td>

                                                        <input type="hidden" name="products[{{ $key+1 }}][unit_id]" value="{{$item->unit_id}}" id="products_{{ $key+1 }}_unit_id">
                                                        <input type="hidden" class="stock_qty" name="products[{{ $key+1 }}][stock_qty]"  
                                                        id="products_{{ $key+1 }}_stock_qty" value="{{$stock_qty}}">
                                                        <input type="hidden" class="net_unit_cost" name="products[{{ $key+1 }}][net_unit_cost]"  
                                                        id="products_{{ $key+1 }}_net_unit_cost" value="{{$item->net_unit_cost}}">
                                                        <input type="hidden" class="sub_total"  name="products[{{ $key+1 }}][subtotal]"  
                                                        id="products_{{ $key+1 }}_subtotal" value="{{$item->total}}">
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @else 
                                        <tr>
                                            <td>
                                                <select name="products[1][id]" id="products_1_id" class="form-control product_id selectpicker" onchange="setProductDetails(1);getProductStockQty(this.value,1);"  data-live-search="true" data-row="1">
                                                    @if (!$products->isEmpty())
                                                    <option value="">Please Select</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-cost="{{ $product->cost }}" data-unitname="{{ $product->unit->unit_name}}" data-unitid="{{ $product->unit->id }}">
                                                            {{ $product->code.' - '.$product->name }}
                                                        </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="unit-name-1 text-center"></td>
                                            <td class="stock-qty-1 text-center"></td>
                                            <td>
                                                <input type="text" class="form-control transfer_qty text-center" name="products[1][transfer_qty]" id="products_1_transfer_qty">
                                            </td>
                                            <td class="net-unit-cost-1 text-center"></td>
                                            <td class="sub-total-1 subtotal text-center"></td>
                                            <td class="text-center"></td>
                        
                                            <input type="hidden" name="products[1][unit_id]" id="products_1_unit_id">
                                            <input type="hidden" class="stock_qty" name="products[1][stock_qty]"  
                                            id="products_1_stock_qty">
                                            <input type="hidden" class="net_unit_cost" name="products[1][net_unit_cost]"  
                                            id="products_1_net_unit_cost">
                                            <input type="hidden" class="sub_total"  name="products[1][subtotal]"  
                                            id="products_1_subtotal">
                                        </tr>
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="3" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ isset($transfer) ? $transfer->total_qty : '0' }}</th>
                                        <th></th>
                                        <th id="total" class="text-center font-weight-bolder">{{ isset($transfer) ? number_format($transfer->grand_total,2,'.','') : '0.00' }}</th>
                                        <th class="text-center"><button type="button" class="btn btn-success btn-sm add-product small-btn"><i class="fas fa-plus-square"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>
                           
                           
                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3">{{ isset($transfer) ? $transfer->note : '' }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <input type="hidden" name="total_qty" value="{{ isset($transfer) ? $transfer->total_qty : '0' }}">
                                <input type="hidden" name="item" value="{{ isset($transfer) ? $transfer->item : '0' }}">
                                <input type="hidden" name="grand_total" value="{{ isset($transfer) ? $transfer->grand_total : '0' }}">
                            </div>
                            <div class="form-grou col-md-12 text-center pt-5">
                                <a href="{{ isset($transfer) ? url('transfer-inventory') : url('transfer-inventory/create') }}" type="button" class="btn btn-danger btn-sm mr-3"><i class="{{ isset($transfer) ? 'fas fa-window-close' : 'fas fa-sync-alt' }}"></i> {{ isset($transfer) ? 'Cancel' : 'Reset' }}</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> {{ isset($transfer) ? 'Update' : 'Save' }}</button>
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
$("#kt_body").addClass("aside-minimize");
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});
    
    var rowindex;

    $('.product_id').on('change', function() {
        var warehouse_id = document.getElementById('from_warehouse_id').value;
        if (!warehouse_id) {
            $(this).val('');
            $(this).selectpicker('refresh');
            notification('error', 'Please select from inventory first!');
        }
    });



    $('#product_table').on('keyup','.transfer_qty',function(){
        rowindex = $(this).closest('tr').index();
        let qty =  parseFloat($(this).val());
        let cost = parseFloat($('#product_table tbody tr:nth-child('+(rowindex + 1)+') .net_unit_cost').val());
        let stock_qty = $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .stock_qty').val() ? parseFloat($('#product_table tbody tr:nth-child('+(rowindex + 1)+') .stock_qty').val()) : 0;
        if($(this).val() <= 0 && $(this).val() != ''){
            qty = 1;
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .transfer_qty').val(1);
            notification('error','Qunatity can\'t be less than 1');
        }
        if(qty > stock_qty)
        {
            qty = stock_qty;
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .transfer_qty').val(stock_qty);
            notification('error','Qunatity can\'t be greater than stock available qty');
        }
        let subtotal = (qty ? qty : 0) * cost;
        console.log(subtotal);
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .subtotal').text(parseFloat(subtotal).toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .sub_total').val(parseFloat(subtotal));
        calculateTotal();
    });

   

    var count = 1;
    @if (isset($transfer))
        @if (!$transfer->hasManyProducts->isEmpty())
        count = "{{ count($transfer->products) }}";
        @endif
    @endif

    function addProductField(count) {

        var newRow = $('<tr>');
        var cols = `<td>
                        <select name="products[${count}][id]" id="products_${count}_id" class="form-control product_id selectpicker" onchange="setProductDetails(${count});getProductStockQty(this.value,${count});"  data-live-search="true" data-row="1">
                            @if (!$products->isEmpty())
                            <option value="">Please Select</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    data-cost="{{ $product->cost }}" data-unitname="{{ $product->unit->unit_name}}" data-unitid="{{ $product->unit->id }}">
                                    {{ $product->code.' - '.$product->name }}
                                </option>
                            @endforeach
                            @endif
                        </select>
                    </td>
                    <td class="unit-name-${count} text-center"></td>
                    <td class="stock-qty-${count} text-center"></td>
                    <td>
                        <input type="text" class="form-control transfer_qty text-center" name="products[${count}][transfer_qty]" id="products_${count}_transfer_qty">
                    </td>
                    <td class="net-unit-cost-${count} text-center"></td>
                    <td class="sub-total-${count} subtotal text-center"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button>
                    </td>

                    <input type="hidden" name="products[${count}][unit_id]" id="products_${count}_unit_id">
                    <input type="hidden" class="stock_qty" name="products[${count}][stock_qty]"  
                    id="products_${count}_stock_qty">
                    <input type="hidden" class="net_unit_cost" name="products[${count}][net_unit_cost]"  
                    id="products_${count}_net_unit_cost">
                    <input type="hidden" class="sub_total"  name="products[${count}][subtotal]"  
                    id="products_${count}_subtotal">`;
        newRow.append(cols);
        $('#product_table tbody').append(newRow);
        $('#product_table tbody .selectpicker').selectpicker();
    }

    $('#product_table').on('click','.remove-product',function(){
        $(this).closest('tr').remove();
        calculateTotal();
    });
    $('#product_table').on('click','.add-product',function(){
        count++;
        addProductField(count);
    });

    function calculateTotal(){
        let total_qty = 0;
        let total = 0;
        $('.transfer_qty').each(function() {
            if($(this).val() == ''){
                total_qty += 0;
            }else{
                total_qty += parseFloat($(this).val());
            }
        });
        $('.sub_total').each(function() {
            if($(this).val() == ''){
                total += 0;
            }else{
                total += parseFloat($(this).val());
            }
        });

        $('#total-qty').text(total_qty);
        $('#total').text(total.toFixed(2));
        $('input[name="total_qty"]').val(total_qty);
        $('input[name="grand_total"]').val(total);
        $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
    }

});

function setProductDetails(row)
{
    var warehouse_id = document.getElementById('from_warehouse_id').value;
    if(warehouse_id)
    {
        const cost     = $(`#products_${row}_id option:selected`).data('cost');
        const unitname = $(`#products_${row}_id option:selected`).data('unitname');
        const unitid   = $(`#products_${row}_id option:selected`).data('unitid');

        $(`.net-unit-cost-${row}`).text(parseFloat(cost ? cost : 0));
        $(`#products_${row}_net_unit_cost`).val(parseFloat(cost ? cost : 0));
        $(`.unit-name-${row}`).text(unitname ? unitname : '');
        $(`#products_${row}_unit_id`).val(unitid ? unitid : '');
    }
}

function getProductStockQty(product_id,row)
{
    var warehouse_id = document.getElementById('from_warehouse_id').value;
    if (warehouse_id) {
        $.ajax({
            url: '{{ url("product/warehouse-wise-qty") }}',
            type: 'POST',
            data: {
                product_id: product_id,_token:_token,warehouse_id:warehouse_id
            },
            success: function(data) {
                $(`.stock-qty-${row}`).text(data);
                $(`#products_${row}_stock_qty`).val(data);
            }
        });
    }
}

function store_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to order table!")
    }else{
        let form = document.getElementById('store_update_form');
        let formData = new FormData(form);
        let url = "{{route('transfer.inventory.store.update')}}";
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
                $('#store_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#store_update_form input#' + key).addClass('is-invalid');
                        $('#store_update_form textarea#' + key).addClass('is-invalid');
                        $('#store_update_form select#' + key).parent().addClass('is-invalid');
                        $('#store_update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        let link = "{{ url('transfer-inventory') }}";
                        if(data.transfer_id){
                            link = "{{ url('transfer-inventory/view') }}/"+data.transfer_id;
                        }
                        window.location.replace(link);
                        
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