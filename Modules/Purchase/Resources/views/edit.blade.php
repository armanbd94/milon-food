@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
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
                    <a href="{{ route('purchase') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <form action="" id="purchase_store_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                            <input type="hidden" name="warehouse_id_hidden" value="{{ $purchase->warehouse_id }}">
                            <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id }}">
                            <input type="hidden" name="purchase_status_hidden" value="{{ $purchase->purchase_status }}">
                            <input type="hidden" name="order_tax_rate_hidden" value="{{ $purchase->order_tax_rate }}">

                            <div class="form-group col-md-4 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $purchase->memo_no }}" readonly />
                            </div>
                            <x-form.textbox labelName="Purchase Date" name="purchase_date" value="{{ $purchase->purchase_date }}" required="required" class="date" col="col-md-4"/>

                            <div class="form-group col-md-4 required">
                                <label for="chalan_no">Supplier</label>
                                <input type="text" class="form-control" value="{{ $purchase->supplier->company_name.' ('.$purchase->supplier->name.')' }}" readonly />
                            </div>

                            <x-form.selectbox labelName="Purchase Status" name="purchase_status" required="required" col="col-md-4" onchange="received_qty(this.value)">
                                @foreach (PURCHASE_STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>

                            <div class="form-group col-md-4">
                                <label for="document">Attach Document <i class="fas fa-info-circle" data-toggle="tooltip" data-theme="dark" title="Maximum Allowed File Size 5MB and Format (png,jpg,jpeg,svg,webp,pdf,csv,xlxs)"></i></label>
                                <input type="file" class="form-control" name="document" id="document">
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center d-none received-material-qty">Received</th>
                                        <th class="text-right">Net Unit Cost</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Tax</th>
                                        {{-- <th class="text-right">Labor Cost</th> --}}
                                        <th class="text-right">Subtotal</th>
                                        <th></th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $temp_unit_name = [];
                                            $temp_unit_operator = [];
                                            $temp_unit_operation_value = [];
                                        @endphp
                                        @if (!$purchase->purchase_materials->isEmpty())
                                            @foreach ($purchase->purchase_materials as $key => $purchase_material)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$purchase_material->pivot->tax_rate)->first();

                                                        $units = DB::table('units')->where('base_unit',$purchase_material->unit_id)
                                                                                    ->orWhere('id',$purchase_material->unit_id)
                                                                                    ->get();

                                                        $unit_name            = [];
                                                        $unit_operator        = [];
                                                        $unit_operation_value = [];

                                                        if($units){
                                                            foreach ($units as $unit) {
                                                                if($purchase_material->pivot->purchase_unit_id == $unit->id)
                                                                {
                                                                    array_unshift($unit_name,$unit->unit_name);
                                                                    array_unshift($unit_operator,$unit->operator);
                                                                    array_unshift($unit_operation_value,$unit->operation_value);
                                                                }else{
                                                                    $unit_name           [] = $unit->unit_name;
                                                                    $unit_operator       [] = $unit->operator;
                                                                    $unit_operation_value[] = $unit->operation_value;
                                                                }
                                                            }

                                                            if($purchase_material->tax_method == 1){
                                                                if($unit_operator[0] == '*'){
                                                                    $material_cost = ($purchase_material->pivot->net_unit_cost + ($purchase_material->pivot->discount / $purchase_material->pivot->qty)) / $unit_operation_value[0];
                                                                }elseif ($unit_operator[0] == '/') {
                                                                    $material_cost = ($purchase_material->pivot->net_unit_cost + ($purchase_material->pivot->discount / $purchase_material->pivot->qty)) * $unit_operation_value[0];
                                                                }
                                                            }else{
                                                                if($unit_operator[0] == '*'){
                                                                    $material_cost = (($purchase_material->pivot->total + ($purchase_material->pivot->discount / $purchase_material->pivot->qty)) / $purchase_material->pivot->qty) / $unit_operation_value[0];
                                                                }elseif ($unit_operator[0] == '/') {
                                                                    $material_cost = (($purchase_material->pivot->total + ($purchase_material->pivot->discount / $purchase_material->pivot->qty)) / $purchase_material->pivot->qty) * $unit_operation_value[0];
                                                                }
                                                            }
                                                            
                                                            $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                            $temp_unit_operator = $unit_operator = implode(",",$unit_operator).',';
                                                            $temp_unit_operation_value = $unit_operation_value = implode(",",$unit_operation_value).',';
                                                        }
                                                    @endphp
                                                    <td data-row="{{ $key + 1 }}">{{ $purchase_material->material_name }}</td>
                    
                                                    <td class="text-center" data-row="{{ $key + 1 }}">{{ $purchase_material->material_code }}</td>
                                                    <td class="unit-name" data-row="{{ $key + 1 }}"></td>
                                                    <td><input type="text" class="form-control qty text-center" name="materials[{{ $key+1 }}][qty]"
                                                        id="materials_{{ $key+1 }}_qty" value="{{ $purchase_material->pivot->qty }}" data-row="{{ $key + 1 }}"></td>
                                                    <td class="received-material-qty d-none"><input type="text" class="form-control received text-center"
                                                            name="materials[{{ $key+1 }}][received]" value="{{ $purchase_material->pivot->received }}" data-row="{{ $key + 1 }}"></td>

                                                    <td><input type="text" class="form-control net-unit-cost text-right net_unit_cost" name="materials[{{ $key+1 }}][net_unit_cost]" value="{{ $purchase_material->pivot->net_unit_cost }}" data-row="{{ $key + 1 }}"></td>
                                                    <td class="discount text-right" data-row="{{ $key + 1 }}">{{ number_format((float)$purchase_material->pivot->discount, 2, '.','') }}</td>
                                                    <td class="tax text-right" data-row="{{ $key + 1 }}">{{ number_format((float)$purchase_material->pivot->tax, 2, '.','') }}</td>
                                                    {{-- <td><input type="text" class="text-right form-control labor_cost labor-cost" name="materials[1][labor_cost]" id="labor_cost_{{ $key + 1 }}" value= "{{ number_format((float)$purchase_material->pivot->labor_cost, 2, '.','') }}" data-row="{{ $key + 1 }}"></td> --}}
                                                    <td class="sub-total text-right" data-row="{{ $key + 1 }}">{{ number_format((float)$purchase_material->pivot->total, 2, '.','') }}</td>
                                                    <td class="text-center"><button type="button" class="edit-material btn btn-sm btn-primary mr-2 small-btn" data-toggle="modal"
                                                        data-target="#editModal"><i class="fas fa-edit"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm remove-material small-btn"><i class="fas fa-trash"></i></button></td>
                                                    <input type="hidden" class="material-id" name="materials[{{ $key+1 }}][id]"  value="{{ $purchase_material->id }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="material-code" name="materials[{{ $key+1 }}][code]" value="{{ $purchase_material->material_code }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="material-cost" name="materials[{{ $key+1 }}][cost]" value="{{ $material_cost }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="purchase-unit material-unit" name="materials[{{ $key+1 }}][unit]" value="{{ $unit_name }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="purchase-unit-operator"  value="{{ $unit_operator }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="purchase-unit-operation-value"  value="{{ $unit_operation_value }}" data-row="{{ $key + 1 }}">                                                    
                                                    <input type="hidden" class="discount-value" name="materials[{{ $key+1 }}][discount]" value="{{ $purchase_material->pivot->discount }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="tax-rate" name="materials[{{ $key+1 }}][tax_rate]" value="{{ $purchase_material->pivot->tax_rate }}" data-row="{{ $key + 1 }}">
                                                    @if ($tax)
                                                    <input type="hidden" class="tax-name" value="{{ $tax->name }}" data-row="{{ $key + 1 }}">
                                                    @else
                                                    <input type="hidden" class="tax-name" value="No Tax" data-row="{{ $key + 1 }}">
                                                    @endif
                                                    <input type="hidden" class="tax-method" value="{{ $purchase_material->tax_method }}" data-row="{{ $key + 1 }}">
                                                    <input type="hidden" class="tax-value" name="materials[{{ $key+1 }}][tax]" value="{{ $purchase_material->pivot->tax }}" data-row="{{ $key + 1 }}">
                                                    {{-- <input type="hidden" class="labor-cost" name="materials[{{ $key+1 }}][labor_cost]" value="{{ $purchase_material->pivot->labor_cost }}" data-row="{{ $key + 1 }}"> --}}
                                                    <input type="hidden" class="subtotal-value" name="materials[{{ $key+1 }}][subtotal]" value="{{ $purchase_material->pivot->total }}" data-row="{{ $key + 1 }}">
                                
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="3" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $purchase->total_qty }}</th>
                                        <th class="d-none received-material-qty font-weight-bolder"></th>
                                        <th></th>
                                        <th id="total-discount" class="text-right font-weight-bolder">{{ number_format($purchase->total_discount,2,'.',',') }}</th>
                                        <th id="total-tax" class="text-right font-weight-bolder">{{ number_format($purchase->total_tax,2,'.',',') }}</th>
                                        {{-- <th id="total-labor-cost" class="text-right font-weight-bolder">{{ number_format($purchase->total_labor_cost,2,'.',',') }}</th> --}}
                                        <th id="total" class="text-right font-weight-bolder">{{ number_format($purchase->total_cost,2,'.',',') }}</th>
                                        <th class="text-center"><button type="button" class="btn btn-success small-btn btn-md add-material"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

                            <x-form.selectbox labelName="Order Tax" name="order_tax_rate" col="col-md-3">
                                <option value="0" selected>No Tax</option>
                                @if (!$taxes->isEmpty())
                                    @foreach ($taxes as $tax)
                                        <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-3">
                                <label for="order_discount">Order Discount</label>
                                <input type="text" class="form-control" name="order_discount" id="order_discount" value="{{ $purchase->order_discount }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="shipping_cost">Shipping Cost</label>
                                <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ $purchase->shipping_cost }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="labor_cost">Labor Cost</label>
                                <input type="text" class="form-control" name="labor_cost" id="labor_cost" value="{{ $purchase->total_labor_cost }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3">{{ $purchase->note }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">0.00</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">0.00</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">0.00</span></th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">0.00</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">0.00</span></th>
                                        <th><strong>Labor Cost</strong><span class="float-right" id="labor_total_cost">0.00</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty" value="{{ $purchase->total_qty }}">
                                <input type="hidden" name="total_discount" value="{{ $purchase->total_discount }}">
                                <input type="hidden" name="total_tax" value="{{ $purchase->total_tax }}">
                                <input type="hidden" name="total_cost" value="{{ $purchase->total_cost }}">
                                <input type="hidden" name="item" value="{{ $purchase->item }}">
                                <input type="hidden" name="order_tax" value="{{ $purchase->order_tax }}">
                                <input type="hidden" name="grand_total" value="{{ $purchase->grand_total }}">
                                <input type="hidden" name="paid_amount" value="{{ $purchase->paid_amount }}">
                            </div>
                            <div class="form-grou col-md-12 text-center pt-5">
                                <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Update</button>
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
<!-- Start :: Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

      <!-- Modal Content -->
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header bg-primary">
          <h3 class="modal-title text-white" id="model-title"></h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <!-- /modal header -->
        <form id="edit_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <x-form.textbox labelName="Quantity" name="edit_qty" required="required" col="col-md-12"/>
                    {{-- <x-form.textbox labelName="Labor Cost" name="edit_labor_cost" col="col-md-12"/> --}}
                    <x-form.textbox labelName="Unit Discount" name="edit_discount" col="col-md-12"/>
                    <x-form.textbox labelName="Unit Cost" name="edit_unit_cost" col="col-md-12"/>
                    @php 
                    $tax_name_all[] = 'No Tax';
                    $tax_rate_all[] = 0;
                    foreach ($taxes as $tax) {
                        $tax_name_all[] = $tax->name;
                        $tax_rate_all[] = $tax->rate;
                    }
                    @endphp
                    <div class="form-group col-md-12">
                        <label for="edit_tax_rate">Tax Rate</label>
                        <select name="edit_tax_rate" id="edit_tax_rate" class="form-control selectpicker">
                            @foreach ($tax_name_all as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="edit_unit">Material Unit</label>
                        <select name="edit_unit" id="edit_unit" class="form-control selectpicker"></select>
                    </div>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="update-btn">Update</button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
</div>
<!-- End :: Edit Modal -->
@endsection

@push('scripts')
<script src="js/jquery-ui.js"></script>
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
//array data depend on warehouse
var material_array = [];
var material_code  = [];
var material_name  = [];
var material_qty   = [];

// array data with selection
var material_cost        = [];
// var material_labor_cost  = [];
var material_discount    = [];
var tax_rate             = [];
var tax_name             = [];
var tax_method           = [];
var unit_name            = [];
var unit_operator        = [];
var unit_operation_value = [];

//temporary array
var temp_unit_name            = [];
var temp_unit_operator        = [];
var temp_unit_operation_value = [];

var rowindex;
var row_material_cost;

$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});

    var rownumber = $('#material_table tbody tr:last').index();
    
    for (rowindex = 0; rowindex <= rownumber; rowindex++) {
        material_cost.push(parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.material-cost').val()));
        var total_discount = parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('td:nth-child(7)').text())
        var quantity = parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.qty').val())
        material_discount.push((total_discount/quantity).toFixed(2));
        // material_labor_cost.push(parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.labor_cost').val()));
        tax_rate.push(parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-rate').val()));
        tax_name.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-name').val());
        tax_method.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-method').val());
        temp_unit_name = $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.purchase-unit').val().split(',');
        unit_name.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.purchase-unit').val());
        unit_operator.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.purchase-unit-operator').val());
        unit_operation_value.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.purchase-unit-operation-value').val());
        $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.purchase-unit').val(temp_unit_name[0]);
        $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.unit-name').text(temp_unit_name[0]);
    }
    //assigning value
    $('select[name="supplier_id"]').val($('input[name="supplier_id_hidden"]').val());
    $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
    $('select[name="purchase_status"]').val($('input[name="purchase_status_hidden"]').val());
    $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');

    $('#item').text($('input[name="item"]').val() + '('+$('input[name="total_qty"]').val()+')');
    $('#subtotal').text(parseFloat($('input[name="total_cost"]').val()).toFixed(2));
    $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed(2));

    if($('#purchase_status option:selected').val() == 2)
    {
        $('.received-material-qty').removeClass('d-none');
    }

    if(!$('input[name="order_discount"]').val())
    {
        $('input[name="order_discount"]').val('0.00');
    }
    $('#order_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed(2));
    if(!$('input[name="shipping_cost"]').val())
    {
        $('input[name="shipping_cost"]').val('0.00');
    }
    $('#shipping_total_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed(2));
    $('#labor_total_cost').text(parseFloat($('input[name="labor_cost"]').val()).toFixed(2));
    $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));

     //Edit Product
     $('#material_table').on('click','.edit-material', function(){
        rowindex = $(this).closest('tr').index();
        //var row_material_name = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(1)').text();
        var row_material_name = "Materials ";
        var row_material_code = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
        $('#model-title').text(row_material_name+'('+row_material_code+')');

        var qty = $(this).closest('tr').find('.qty').val();
        $('#edit_qty').val(qty);
        // $('#edit_labor_cost').val(parseFloat(material_labor_cost[rowindex]).toFixed(2));
        $('#edit_discount').val(parseFloat(material_discount[rowindex]).toFixed(2));

        unitConversion();
        $('#edit_unit_cost').val(row_material_cost.toFixed(2));

        var tax_name_all = <?php echo json_encode($tax_name_all); ?>;
        var pos = tax_name_all.indexOf(tax_name[rowindex]);
        $('#edit_tax_rate').val(pos);

        temp_unit_name = (unit_name[rowindex]).split(',');
        temp_unit_name.pop();
        temp_unit_operator = (unit_operator[rowindex]).split(',');
        temp_unit_operator.pop();
        temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
        temp_unit_operation_value.pop();

        $('#edit_unit').empty();

        $.each(temp_unit_name, function(key,value){
            $('#edit_unit').append('<option value="'+key+'">'+value+'</option>');
        });
        $('.selectpicker').selectpicker('refresh');
    });

    //Update Edit Product Data
    $('#update-btn').on('click',function(){
        var edit_discount  = $('#edit_discount').val();
        var edit_qty       = $('#edit_qty').val();
        var edit_unit_cost = $('#edit_unit_cost').val();

        if(parseFloat(edit_discount) > parseFloat(edit_unit_cost))
        {
            notification('error','Invalid discount input');
            return;
        }

        if(edit_qty < 1)
        {
            $('#edit_qty').val(1); 
            edit_qty = 1;
            notification('error','Quantity can\'t be less than 1');
        }

        var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
        row_unit_operation_value = parseFloat(row_unit_operation_value);
        var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;

        tax_rate[rowindex] = parseFloat(tax_rate_all[$('#edit_tax_rate option:selected').val()]);
        tax_name[rowindex] = $('#edit_tax_rate option:selected').text();

        if(row_unit_operator == '*')
        {
            material_cost[rowindex] = $('#edit_unit_cost').val() / row_unit_operation_value;
        }else{
            material_cost[rowindex] = $('#edit_unit_cost').val() * row_unit_operation_value;
        }
        
        // material_labor_cost[rowindex] = $('#edit_labor_cost').val();
        material_discount[rowindex] = $('#edit_discount').val();
        var position = $('#edit_unit').val();
        var temp_operator = temp_unit_operator[position];
        var temp_operation_value = temp_unit_operation_value[position];
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.purchase-unit').val(temp_unit_name[position]);
        temp_unit_name.splice(position,1);
        temp_unit_operator.splice(position,1);
        temp_unit_operation_value.splice(position,1);

        temp_unit_name.unshift($('#edit_unit option:selected').text());
        temp_unit_operator.unshift(temp_operator);
        temp_unit_operation_value.unshift(temp_operation_value);

        unit_name[rowindex] = temp_unit_name.toString() + ',';
        unit_operator[rowindex] = temp_unit_operator.toString() + ',';
        unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
        checkQuantity(edit_qty,false,input=2);
    });

    $('#material_table').on('keyup','.qty',function(){
        rowindex = $(this).closest('tr').index();
        if($(this).val() < 1 && $(this).val() != ''){
            $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
            notification('error','Qunatity can\'t be less than 1');
        }
        checkQuantity($(this).val(),true,input=2);
    });
    
    $('#material_table').on('keyup','.net_unit_cost',function(){
        rowindex = $(this).closest('tr').index();
        if($(this).val() < 1 && $(this).val() != ''){
            $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .net_unit_cost').val(1);
            notification('error','Net unit price can\'t be less than 1');
        }else{
            material_cost[rowindex] = $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .net_unit_cost').val();
        }
        var qty = $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val();
        if(qty > 0){
            checkQuantity(qty,true,input=1);
        }
        
    });
    
    // $('#material_table').on('keyup','.labor_cost',function(){
    //     rowindex = $(this).closest('tr').index();
    //     if($(this).val() <= 0 && $(this).val() == ''){
    //         $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .labor_cost ').val(0);
    //         notification('error','Labor Cost can\'t be less than 0 OR Empty');
    //     }else{
    //         material_labor_cost[rowindex] = $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .labor_cost').val();
    //     }
    //     var qty = $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val();
    //     if(qty > 0){
    //         checkQuantity(qty,true,input=1);
    //     }
        
    // });

    $('#material_table').on('click','.remove-material',function(){
        rowindex = $(this).closest('tr').index();
        material_cost.splice(rowindex,1);
        // material_labor_cost.splice(rowindex,1);
        material_discount.splice(rowindex,1);
        tax_rate.splice(rowindex,1);
        tax_name.splice(rowindex,1);
        tax_method.splice(rowindex,1);
        unit_name.splice(rowindex,1);
        unit_operator.splice(rowindex,1);
        unit_operation_value.splice(rowindex,1);
        $(this).closest('tr').remove();
        calculateTotal();
    });

    @if (!$purchase->purchase_materials->isEmpty())
    var count = "{{ count($purchase->purchase_materials) + 1 }}" ;
    @else 
    var count = 1;
    @endif

    
    $('#material_table').on('click','.add-material',function(){
        count++;
        material_row_add(count);
    });   
       
    function material_row_add(count){
        var newRow = $('<tr>');
        var cols = '';
        cols += `<td><select name="materials[`+count+`][material_id]" id="material_list_${count}" class="fcs col-md-12 material_name form-control" onchange="getMaterialDetails(this,${count})"  data-live-search="true" data-row="${count}">                                            
                                    @if (!$materials->isEmpty())
                                        <option value="0">Please Select</option>
                                    @foreach ($materials as $material)
                                        <option value="{{ $material->id }}">{{ $material->material_name.' ('.$material->material_code.')'; }}</option>
                                    @endforeach
                                    @endif
                                </select></td>`;
        
        cols += `<td class="text-center material-code_tx_${count}"  id="material_code_${count}" data-row="${count}"></td>`;
        cols += `<td class="unit-name_${count} text-center" id="unit_name_${count}" data-row="${count}"></td>`;
        cols += `<td><input type="text" class="form-control qty text-center" name="materials[`+count+`][qty]"
            id="materials_`+count+`_qty" value="1" data-row="${count}"></td>`;

        if($('#purchase_status option:selected').val() == 1)
        {
            cols += `<td class="received-material-qty d-none"><input type="text" class="form-control received text-center"
                name="materials[`+count+`][received]" value="1" data-row="${count}"></td>`;

        }else if($('#purchase_status option:selected').val() == 2){

            cols += `<td class="received-material-qty"><input type="text" class="form-control received text-center"
                name="materials[`+count+`][received]" value="1" data-row="${count}"></td>`;
        }else{
            cols += `<td class="received-material-qty d-none"><input type="text" class="form-control received text-center"
                name="materials[`+count+`][received]" value="0" data-row="${count}"></td>`;
        }

        cols += `<td><input type="text" class="net_unit_cost form-control text-right" name="materials[`+count+`][net_unit_cost]" id="materials_net_unit_cost_${count}" data-row="${count}"></td>`;
        cols += `<td class="discount text-right" data-row="${count}"></td>`;
        cols += `<td class="tax text-right" data-row="${count}"></td>`;
        // cols += `<td><input type="text" class="labor_cost labor-cost form-control text-right" name="materials[`+count+`][labor_cost]"  id="labor_cost_${count}" data-row="${count}"></td>`;
        cols += `<td class="sub-total text-right" data-row="${count}"></td>`;
        cols += `<td class="text-center">
                    <button type="button" class="edit-material btn btn-sm btn-primary mr-2 small-btn d-none" data-toggle="modal" id="edit_modal_${count}" data-target="#editModal"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-danger btn-sm remove-material small-btn"><i class="fas fa-trash"></i></button>
                </td>`;
        cols += `<input type="hidden" class="material-id" name="materials[`+count+`][id]" id="material_id_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="material-code" name="materials[`+count+`][code]" id="material_code_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="material-unit" name="materials[`+count+`][unit]" id="material_unit_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="discount-value" name="materials[`+count+`][discount]" id="discount_value_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="tax-rate" name="materials[`+count+`][tax_rate]" id="tax_rate_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="tax-value" name="materials[`+count+`][tax]" id="tax_value_${count}" data-row="${count}">`;
        cols += `<input type="hidden" class="subtotal-value" name="materials[`+count+`][subtotal]" id="subtotal_value_${count}" data-row="${count}">`;

        newRow.append(cols);
        $('#material_table tbody').append(newRow);
        $('#material_table .selectpicker').selectpicker();
    } 

    $('input[name="order_discount"]').on('input',function(){
        calculateGrandTotal();
    });
    $('input[name="shipping_cost"]').on('input',function(){
        calculateGrandTotal();
    });
    $('input[name="labor_cost"]').on('input',function(){
        calculateGrandTotal();
    });
    $('select[name="order_tax_rate"]').on('change',function(){
        calculateGrandTotal();
    });



}); 

function getMaterialDetails(value,rowindex){
    //alert($(value).val());
    materialSearch($(value).val(),rowindex);

} 
    
function materialSearch(data,row) {       
    rowindex = $('#material_list_'+row).closest('tr').index();
    var temp_data = $('#material_list_'+row).val();

        $.ajax({
            url: '{{ route("material.search.by.id") }}',
            type: 'POST',
            data: {
                data: data,_token:_token
            },
            success: function(data) {
                
                $("#edit_modal_"+row).removeClass("d-none");
                temp_unit_name = data.unit_name.split(',');
                $('#material_code_'+row).text(data.code);
                $('#unit_name_'+row).text(temp_unit_name[0]);
                $('#materials_net_unit_cost_'+row).val(data.cost);
                $('.tax'+row).text(data.tax_name);
                $('#material_id_'+row).val(data.id);
                $('#material_unit_'+row).val(temp_unit_name[0]);
                $('#tax_rate_'+row).val(data.tax_rate);

                if(material_cost[rowindex] == 'undefined'){
                    material_cost.push(parseFloat(data.cost));
                }else{
                    material_cost[rowindex] = parseFloat(data.cost);
                }
                console.log(material_cost[rowindex]);

                // material_labor_cost.push('0.00');
                material_discount.push('0.00');
                tax_rate.push(parseFloat(data.tax_rate));
                tax_name.push(data.tax_name);
                tax_method.push(data.tax_method);
                unit_name.push(data.unit_name);
                unit_operator.push(data.unit_operator);
                unit_operation_value.push(data.unit_operation_value);
                checkQuantity(1,true,input=2);
            }
        });
}


function checkQuantity(purchase_qty,flag,input=2){
        var row_material_code = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
        var pos = material_code.indexOf(row_material_code);
        var operator = unit_operator[rowindex].split(',');
        var operation_value = unit_operation_value[rowindex].split(',');

        if(operator[0] == '*')
        {
            total_qty = purchase_qty * operation_value[0];
        }else if(operator[0] == '/'){
            total_qty = purchase_qty / operation_value[0];
        }

        $('#editModal').modal('hide');
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(purchase_qty);
        var status = $('#purchase_status option:selected').val();
        if(status == '1' || status == '2'){
            $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.received').val(purchase_qty);
        }
        calculateProductData(purchase_qty,input);

    }

function calculateProductData(quantity,input=2){ 
    unitConversion();

    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(7)').text((material_discount[rowindex] * quantity).toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.discount-value').val((material_discount[rowindex] * quantity).toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.unit-name').text(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.material-unit').val(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));
    //console.log(material_cost);
    //console.log(row_material_cost,material_labor_cost[rowindex]);
    if(tax_method[rowindex] == 1)
    {
        var net_unit_cost = row_material_cost - material_discount[rowindex];
        var tax = net_unit_cost * quantity * (tax_rate[rowindex]/100);
        var sub_total = (net_unit_cost * quantity) + tax;

    }else{
        var sub_total_unit = row_material_cost - material_discount[rowindex];
        var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_cost) * quantity;
        var sub_total = (sub_total_unit * quantity);
    }

    if(input==2){
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(6)').val(net_unit_cost.toFixed(2));
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.net_unit_cost').val(net_unit_cost.toFixed(2));
    }
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(8)').text(tax.toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-value').val(tax.toFixed(2));
    
    // if(input==2){
    //     $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').val(parseFloat(material_labor_cost[rowindex]).toFixed(2));
    //     $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.labor-cost').val(parseFloat(material_labor_cost[rowindex]).toFixed(2));
    // }
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').text(sub_total.toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.subtotal-value').val(sub_total.toFixed(2));

    calculateTotal();
}

function unitConversion()
{
    var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
    row_unit_operation_value = parseFloat(row_unit_operation_value);
    if(row_unit_operator == '*')
    {
        row_material_cost = material_cost[rowindex] * row_unit_operation_value;
    }else{
        row_material_cost = material_cost[rowindex] / row_unit_operation_value;
    }
}

function calculateTotal()
{
    //sum of qty
    var total_qty = 0;
    $('.qty').each(function() {
        if($(this).val() == ''){
            total_qty += 0;
        }else{
            total_qty += parseFloat($(this).val());
        }
    });
    $('#total-qty').text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    //sum of discount
    var total_discount = 0;
    $('.discount').each(function() {
        total_discount += parseFloat($(this).text());
    });
    $('#total-discount').text(total_discount.toFixed(2));
    $('input[name="total_discount"]').val(total_discount.toFixed(2));

    //sum of tax
    var total_tax = 0;
    $('.tax').each(function() {
        total_tax += parseFloat($(this).text());
    });
    $('#total-tax').text(total_tax.toFixed(2));
    $('input[name="total_tax"]').val(total_tax.toFixed(2));

    //sum of labor cost
    // var total_labor_cost = 0;
    // $('.labor_cost').each(function() {
    //     total_labor_cost += parseFloat($(this).val());
    // });
    // $('#total-labor-cost').text(total_labor_cost.toFixed(2));
    // $('input[name="total_labor_cost"]').val(total_labor_cost.toFixed(2));

    //sum of subtotal
    var total = 0;
    $('.sub-total').each(function() {
        total += parseFloat($(this).text());
    });
    $('#total').text(total.toFixed(2));
    $('input[name="total_cost"]').val(total.toFixed(2));

    calculateGrandTotal();
}

function calculateGrandTotal()
{
    var item = $('#material_table tbody tr:last').index();
    var total_qty = parseFloat($('#total-qty').text());
    var subtotal = parseFloat($('#total').text());
    var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
    var order_discount = parseFloat($('#order_discount').val());
    var shipping_cost = parseFloat($('#shipping_cost').val());
    var labor_cost = parseFloat($('#labor_cost').val());
    if(!order_discount){
        order_discount = 0.00;
    }
    if(!shipping_cost){
        shipping_cost = 0.00;
    }
    if(!labor_cost){
        labor_cost = 0.00;
    }
    item = ++item + '(' + total_qty + ')';
    order_tax = (subtotal - order_discount) * (order_tax / 100);
    var grand_total = (subtotal + order_tax + shipping_cost+labor_cost) - order_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('#material_table tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed(2));
    $('#order_total_tax').text(order_tax.toFixed(2));
    $('input[name="order_tax"]').val(order_tax.toFixed(2));
    $('#order_total_discount').text(order_discount.toFixed(2));
    $('#shipping_total_cost').text(shipping_cost.toFixed(2));
    $('#labor_total_cost').text(labor_cost.toFixed(2));
    $('#grand_total').text(grand_total.toFixed(2));
    $('input[name="grand_total"]').val(grand_total.toFixed(2));
}
received_qty("{{ $purchase->purchase_status }}");

function received_qty(purchase_status)
{
    if(purchase_status == 2){
        $(".received-material-qty").removeClass("d-none");
        $(".qty").each(function() {
            rowindex = $(this).closest('tr').index();
            $('table#material_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
        });

    }else if((purchase_status == 3) || (purchase_status == 4)){
        $(".received-material-qty").addClass("d-none");
        $(".recieved").each(function() {
            $(this).val(0);
        });
    }else {
        $(".received-material-qty").addClass("d-none");
        $(".qty").each(function() {
            rowindex = $(this).closest('tr').index();
            $('table#material_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
        });
    }
}


function store_data(){
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('purchase_store_form');
        let formData = new FormData(form);
        let url = "{{route('purchase.update')}}";
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
                $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                $('#purchase_store_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#purchase_store_form input#' + key).addClass('is-invalid');
                        $('#purchase_store_form textarea#' + key).addClass('is-invalid');
                        $('#purchase_store_form select#' + key).parent().addClass('is-invalid');
                        $('#purchase_store_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('purchase') }}");
                        
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