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
    .w18{width: 18%;}
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
                            <div class="form-group col-md-4 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $memo_no }}" readonly />
                            </div>
                            <x-form.textbox labelName="Purchase Date" name="purchase_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>

                            <x-form.selectbox labelName="Supplier" name="supplier_id" required="required" col="col-md-4">
                                @if (!$suppliers->isEmpty())
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach 
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Purchase Status" name="purchase_status" required="required" col="col-md-4" onchange="received_qty(this.value)">
                                @foreach (PURCHASE_STATUS as $key => $value)
                                    <option value="{{ $key }}" {{ ($key == 1) ? 'selected' : '' }}>{{ $value }}</option>
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
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <td class="col-md-3">                                                  
                                            <select name="materials[1][material_id]" id="material_list_1" class="fcs col-md-12 material_name form-control" onchange="getMaterialDetails(this,1)"  data-live-search="true" data-row="1">                                            
                                                @if (!$materials->isEmpty())
                                                    <option value="0">Please Select</option>
                                                @foreach ($materials as $material)
                                                    <option value="{{ $material->id }}">{{ $material->material_name.' ('.$material->material_code.')'; }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </td>                                        
                                        <td class="material-code_tx_1 text-center" id="material_code_1"  data-row="1"></td>
                                        <td class="unit-name_1 text-center"  id="unit_name_1"  data-row="1"></td>
                                        <td><input type="text" class="form-control qty text-center" name="materials[1][qty]"
                                            id="materials_qty_1" value="1"  data-row="1">
                                        </td>
                                        <td class="received-material-qty d-none"><input type="text" class="form-control received text-center"
                                                name="materials[1][received]" value="1" data-row="1"></td>

                                        <td><input type="text" class="text-right form-control net_unit_cost" name="materials[1][net_unit_cost]" id="materials_net_unit_cost_1" data-row="1"></td>
                                        <td class="discount text-right" data-row="1"></td>
                                        <td class="tax text-right" data-row="1"></td>
                                        {{-- <td><input type="text" class="text-right form-control labor_cost labor-cost" name="materials[1][labor_cost]" id="labor_cost_1" data-row="1"></td> --}}
                                        <td class="sub-total text-right" data-row="1"></td>
                                        <td class="text-center" data-row="1"><button type="button" class="edit-material btn btn-sm small-btn btn-primary mr-2 small-btn d-none"  id="edit_modal_1" data-toggle="modal" data-target="#editModal"><i class="fas fa-edit"></i></button></td>
                                        <input type="hidden" class="material-id_1" id="material_id_1" name="materials[1][id]" data-row="1">
                                        <input type="hidden" class="material-code_1" id="material_code_1" name="materials[1][code]" data-row="1">
                                        <input type="hidden" class="material-unit_1" id="material_unit_1" name="materials[1][unit]" data-row="1">
                                        <input type="hidden" class="discount-value" id="discount_value_1" name="materials[1][discount]" data-row="1">
                                        <input type="hidden" class="tax-rate" id="tax_rate_1" name="materials[1][tax_rate]" data-row="1">
                                        <input type="hidden" class="tax-value" id="tax_value_1" name="materials[1][tax]" data-row="1">
                                        <input type="hidden" class="subtotal-value" id="subtotal_value_1" name="materials[1][subtotal]" data-row="1">
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="3" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th class="d-none received-material-qty font-weight-bolder"></th>
                                        <th></th>
                                        <th id="total-discount" class="text-right font-weight-bolder">0.00</th>
                                        <th id="total-tax" class="text-right font-weight-bolder">0.00</th>
                                        {{-- <th id="total-labor-cost" class="text-right font-weight-bolder">0.00</th> --}}
                                        <th id="total" class="text-right font-weight-bolder">0.00</th>
                                        <th class="text-center"><button type="button" class="btn btn-success small-btn btn-md add-material"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <div class="row" style="justify-content: space-between;padding: 10px 20px;">
                                    <x-form.selectbox labelName="Order Tax" name="order_tax_rate" col="w18">
                                        <option value="0" selected>No Tax</option>
                                        @if (!$taxes->isEmpty())
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                            @endforeach
                                        @endif
                                    </x-form.selectbox>
        
                                    <div class="form-group w18">
                                        <label for="order_discount">Order Discount</label>
                                        <input type="text" class="form-control" name="order_discount" id="order_discount">
                                    </div>
                                    <div class="form-group w18">
                                        <label for="shipping_cost">Shipping Cost</label>
                                        <input type="text" class="form-control" name="shipping_cost" id="shipping_cost">
                                    </div>
                                    <div class="form-group w18">
                                        <label for="labor_cost">Labor Cost</label>
                                        <input type="text" class="form-control" name="labor_cost" id="labor_cost">
                                    </div>
                                    <x-form.selectbox labelName="Payment Status" name="payment_status" required="required"  col="w18">
                                        @foreach (PAYMENT_STATUS as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3"></textarea>
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
                                <input type="hidden" name="total_qty">
                                <input type="hidden" name="total_discount">
                                <input type="hidden" name="total_tax">
                                <input type="hidden" name="total_cost">
                                <input type="hidden" name="item">
                                <input type="hidden" name="order_tax">
                                <input type="hidden" name="grand_total">
                            </div>
                            <div class="payment col-md-12 d-none">
                                <div class="row">
                                    <div class="form-group col-md-3 required">
                                        <label for="paid_amount">Paid Amount</label>
                                        <input type="text" class="form-control" name="paid_amount" id="paid_amount">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="due_amount">Due Amount</label>
                                        <input type="text" class="form-control" id="due_amount" readonly>
                                    </div>
                                    <x-form.selectbox labelName="Payment Method" name="payment_method" required="required"  col="col-md-3">
                                        @foreach (PAYMENT_METHOD as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-3"/>
                                    <div class="form-group col-md-3 d-none cheque_number required">
                                        <label for="cheque_number">Cheque No.</label>
                                        <input type="text" class="form-control" name="cheque_number" id="cheque_number">
                                    </div>
                                </div>
                            
                            </div>
                           
                            <div class="form-grou col-md-12 text-center pt-5">
                                <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
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
var row_material_cost=0;

$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});

     //Edit Product
     $('#material_table').on('click','.edit-material', function(){
        rowindex = $(this).closest('tr').index();
        var row_material_name = "Meterials";
        //var row_material_name = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(1)').text();
        var row_material_code = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
        $('#model-title').text(row_material_name+'('+row_material_code+')');

        var qty = $(this).closest('tr').find('.qty').val();
        $('#edit_qty').val(qty);
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

        console.log(material_cost);

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
        checkQuantity(edit_qty,false);
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

    $('#payment_status').on('change',function(){
        if($(this).val() != 3){
            $('.payment').removeClass('d-none');
            $('#paid_amount').val($('input[name="grand_total"]').val());
            $('#due_amount').val(parseFloat(0).toFixed(2));
        }else{
            $('#paid_amount').val(0);
            $('#due_amount').val(parseFloat($('input[name="grand_total"]').val()).toFixed(2));
            $('.payment').addClass('d-none');
        }
    });

    $(document).on('change', '#payment_method', function () {
        if($('#payment_method option:selected').val() == 2)
        {
            $('.cheque_number').removeClass('d-none');
        }else{
            $('.cheque_number').addClass('d-none');
        }
        $.ajax({
            url: "{{route('account.list')}}",
            type: "POST",
            data: { payment_method: $('#payment_method option:selected').val(),_token: _token},
            success: function (data) {
                $('#purchase_store_form #account_id').html('');
                $('#purchase_store_form #account_id').html(data);
                $('#purchase_store_form #account_id.selectpicker').selectpicker('refresh');
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });

    $('#paid_amount').on('input',function(){
        var payable_amount = parseFloat($('input[name="grand_total"]').val());
        var paid_amount = parseFloat($(this).val());
        
        if(paid_amount > payable_amount){
            $('#paid_amount').val(payable_amount.toFixed(2));
            notification('error','Paid amount cannot be bigger than grand total amount');
        }
        $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));
        
    });
    
    var count = 1;
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
        //cols += `<td><input type="text" class="labor_cost labor-cost form-control text-right" name="materials[`+count+`][labor_cost]"  id="labor_cost_${count}" data-row="${count}"></td>`;
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

    //console.log(`net_unit_cost = ${net_unit_cost}`);

    //$('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(6)').val(net_unit_cost.toFixed(2));
    if(input==2){
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.net_unit_cost').val(net_unit_cost.toFixed(2));
    }
    
    // if(input==2){
    //     $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').val(parseFloat(material_labor_cost[rowindex]).toFixed(2));
    //     $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.labor-cost').val(parseFloat(material_labor_cost[rowindex]).toFixed(2));
    // }
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(8)').text(tax.toFixed(2));
    $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-value').val(tax.toFixed(2));
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
    var grand_total = (subtotal + order_tax + shipping_cost + labor_cost) - order_discount;

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
    if($('#payment_status option:selected').val() == 1)
    {
        $('#paid_amount').val(grand_total.toFixed(2));
    }else if($('#payment_status option:selected').val() == 2){
        $('#paid_amount').val(grand_total.toFixed(2));
        var paid_amount = $('#paid_amount').val();
        $('#due_amount').val(parseFloat(grand_total-paid_amount).toFixed(2));
    }else{
        $('#paid_amount').val('0');
        $('#due_amount').val(parseFloat(grand_total).toFixed(2));
    }

}

function received_qty(purchase_status)
{
    if(purchase_status == 2){
        $(".received-material-qty").removeClass("d-none");
        $(".qty").each(function() {
            rowindex = $(this).closest('tr').index();
            $('table#material_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
        });

    }
    else if((purchase_status == 3) || (purchase_status == 4)){
        $(".received-material-qty").addClass("d-none");
        $(".recieved").each(function() {
            $(this).val(0);
        });
    }
    else {
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
        let url = "{{route('purchase.store')}}";
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
                        window.location.replace("{{ url('purchase/view') }}/"+data.purchase_id);
                        
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