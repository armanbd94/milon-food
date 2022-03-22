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
                    <a href="{{ route('purchase.return') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <form id="purchase_return_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id }}">
                            <input type="hidden" class="form-control" name="warehouse_id" value="{{ $purchase->warehouse_id }}" />

                            <div class="form-group col-md-3 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $purchase->memo_no }}"  readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="purchase_date">Purchased Date</label>
                                <input type="text" class="form-control" name="purchase_date" id="purchase_date" value="{{ $purchase->purchase_date }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="return_date">Return Date</label>
                                <input type="text" class="form-control date" name="return_date" id="return_date" value="{{ date('Y-m-d') }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="">Supplier Name</label>
                                <input type="text" class="form-control" name="supplier_name" value="{{ $purchase->supplier->name }}" readonly />
                            </div>

                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th >Name</th>
                                        <th  class="text-center">Code</th>
                                        <th  class="text-center">Unit</th>
                                        <th  class="text-center">Purchase Qty</th>
                                        <th  class="text-center">Return Qty</th>
                                        <th  class="text-right">Net Unit Cost</th>
                                        <th  class="text-right">Deduction (%)</th>
                                        <th  class="text-right">Subtotal</th>
                                        <th>Check Return</th>
                                    </thead>
                                    <tbody>
                                        @if (!$purchase->purchase_materials->isEmpty())
                                            @foreach ($purchase->purchase_materials as $key => $purchase_material)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$purchase_material->pivot->tax_rate)->first();

                                                        $unit_name = DB::table('units')->where('id',$purchase_material->pivot->purchase_unit_id)->value('unit_name');
                                                        
                                                        $total_return_qty = \DB::table('purchase_return_materials')
                                                        ->where('memo_no',$purchase->memo_no)
                                                        ->where('material_id',$purchase_material->pivot->material_id)->sum('return_qty');;
                                                        $purchase_qty = $purchase_material->pivot->qty - $total_return_qty;
                                                    @endphp
                                                    <td>{{ $purchase_material->material_name }}</td>
                                                    <td class="text-center">{{ $purchase_material->material_code }}</td>
                                                    <td class="unit-name text-center">{{ $unit_name }}</td>
                                                    <td><input type="text" class="purchase_qty_{{ $key+1 }} form-control text-center" name="materials[{{ $key+1 }}][purchase_qty]"  value="{{ $purchase_qty }}" readonly></td>
                                                    <td><input type="text" class="form-control return_qty_{{ $key+1 }} text-center" onkeyup="quantity_calculate('{{ $key+1 }}')" onchange="quantity_calculate('{{ $key+1 }}')" name="materials[{{ $key+1 }}][return_qty]" id="products_{{ $key+1 }}_return_qty" placeholder="0"></td>
                                                    <td><input type="text" class="net_unit_cost_{{ $key+1 }} form-control text-right" name="materials[{{ $key+1 }}][net_unit_cost]" value="{{ $purchase_material->pivot->new_unit_cost }}"></td>
                                                    <td><input type="text" class="deduction_rate_{{ $key+1 }} form-control text-right" onkeyup="quantity_calculate('{{ $key+1 }}')" onchange="quantity_calculate('{{ $key+1 }}')" name="materials[{{ $key+1 }}][deduction_rate]" placeholder="0.00"></td>
                                                    <td class="sub-total sub-total-{{ $key+1 }} text-right"></td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="hidden" id="return_{{ $key+1 }}"  name="materials[{{ $key+1 }}][return]" value="2">
                                                            <input type="checkbox" class="custom-control-input chk" onchange="setReturnValue('{{ $key+1 }}')"  id="products_{{ $key+1 }}_return_checkbox">
                                                            <label class="custom-control-label" for="products_{{ $key+1 }}_return_checkbox"></label>
                                                        </div>
                                                    </td>
                                                    <input type="hidden" class="product-id" name="materials[{{ $key+1 }}][id]"  value="{{ $purchase_material->pivot->material_id }}">
                                                    <input type="hidden" class="product-code" name="materials[{{ $key+1 }}][code]" value="{{ $purchase_material->material_code }}">
                                                    <input type="hidden" class="purchase-unit material-unit" name="materials[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="deduction_amount deduction_amount_{{ $key+1 }}" name="materials[{{ $key+1 }}][deduction_amount]" >
                                                    <input type="hidden" class="subtotal subtotal_{{ $key+1 }}" name="materials[{{ $key+1 }}][total]" >
                                
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" rowspan="3">
                                                <label  for="reason">Reason</label>
                                                <textarea class="form-control" name="reason" id="reason"></textarea><br>
                                            </td>
                                            <td class="text-right" colspan="1"><b>Total Deduction</b></td>
                                            <td class="text-right">
                                                <input type="text" id="total_deduction_ammount" class="form-control text-right" placeholder="0.00" name="total_deduction" readonly="readonly" />
                                            </td>
                                        </tr>
                                        <tr class="d-none">
                                            <td class="text-right" colspan="1" ><b>Total Tax</b></td>
                                            <td class="text-right">
                                                <input type="hidden" name="total_price" id="total_price">
                                                <input type="hidden" name="tax_rate" id="tax_rate" value="{{ $purchase->order_tax_rate ? $purchase->order_tax_rate : 0 }}">
                                                <input id="total_tax_ammount" tabindex="-1" class="form-control text-right valid" name="total_tax" placeholder="0.00" readonly="readonly" aria-invalid="false" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"  class="text-right"><b>Net Return</b></td>
                                            <td class="text-right">
                                                <input type="text" id="grandTotal" class="form-control text-right" name="grand_total_price" placeholder="0.00" readonly="readonly" />
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="form-grou col-md-12 text-right pt-5">
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="save_data()">Return</button>
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
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    $('#save-btn').prop("disabled", true);
    $('input:checkbox').click(function () {
        if ($(this).is(':checked')) {
            $('#save-btn').prop("disabled", false);
        } else {
            if ($('.chk').filter(':checked').length < 1) {
                $('#save-btn').attr('disabled', true);
            }
        }
    });
});

function setReturnValue(row)
{
    $('#products_'+row+'_return_checkbox').is(':checked') ? $('#return_'+row).val(1) : $('#return_'+row).val(2); 
}

function quantity_calculate(row) {
    var a = 0,o = 0,d = 0,p = 0;
    var purchase_qty = $(".purchase_qty_" + row).val();
    var return_qty = $(".return_qty_" + row).val() ? $(".return_qty_" + row).val() : 0;
    var price_item = $(".net_unit_cost_" + row).val();
    var deduction_rate = $(".deduction_rate_" + row).val();
    if(parseFloat(purchase_qty) < parseFloat(return_qty)){
        alert("Purchase quantity less than quantity!");
        $("#return_qty_"+row).val("");
    }
    
    var price = (return_qty * price_item);
    var deduction = price * (deduction_rate / 100);
    $(".deduction_amount_" + row).val(deduction);
    var deduction_amount = $(".deduction_amount_" + row).val();

    //Total price calculate per product
    var temp = price - deduction_amount;
    $(".subtotal_" + row).val(temp);
    $(".sub-total-" + row).text(parseFloat(temp).toFixed(2));

    $(".subtotal").each(function () {
        isNaN(this.value) || o == this.value.length || (a += parseFloat(this.value));
    });
    var tax_rate = parseFloat($('#tax_rate').val());
    var total_tax_ammount = a * (tax_rate/100);
    var grand_total = a + total_tax_ammount;
    $("#total_price").val(a.toFixed(2, 2));
    $("#total_tax_ammount").val(total_tax_ammount.toFixed(2, 2));
    $("#grandTotal").val(grand_total.toFixed(2, 2));

    $(".deduction_amount").each(function () {
        isNaN(this.value) || p == this.value.length || (d += parseFloat(this.value));
    });
    $("#total_deduction_ammount").val(d.toFixed(2, 2));
    

}

function save_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to return table!")
    }else{
        let form = document.getElementById('purchase_return_form');
        let formData = new FormData(form);
        let url = "{{route('purchase.return.store')}}";
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
                $('#purchase_return_form').find('.is-invalid').removeClass('is-invalid');
                $('#purchase_return_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#purchase_return_form input#' + key).addClass('is-invalid');
                        $('#purchase_return_form textarea#' + key).addClass('is-invalid');
                        $('#purchase_return_form select#' + key).parent().addClass('is-invalid');
                        $('#purchase_return_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('purchase.return') }}");
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