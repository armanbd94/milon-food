@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    #form-tab li a.active{
        background: #034d97 !important;
        color: white !important;
    }
    .nav-link{
        position: relative;
        border-radius: 5px !important;
        background: #E4E6EF;
        color: #7E8299;
    }
    .remove-tab{
        position: absolute;
        top: -10px;
        right: -3px;
        border-radius: 50%;
        font-size: 20px;
    }
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
                    <button type="button" class="btn btn-primary btn-sm mr-5" onclick="check_material_stock()" id="save-btn"><i class="fas fa-save"></i> Save</button>
                    <a href="{{ route('production') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="col-md-12">
                        <div class="row">
                            <x-form.textbox labelName="Batch No." name="batch_no" value="{{ $batch_no }}" required="required" property="readonly" col="col-md-4"/>
                            <x-form.textbox labelName="Date" name="start_date" required="required" col="col-md-4" class="date" property="readonly" value="{{ date('Y-m-d') }}"/>
                            <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required"  col="col-md-4" class="selectpicker">
                                @if (!$warehouses->isEmpty())
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $warehouse->id == 1 ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                        </div>
                    </div>
                    <div class="col-md-12 pt-5">
                        <ul class="nav nav-tabs nav-tabs-2" id="form-tab" role="tablist" style="border-bottom: 0px !important;justify-content: space-between;">
                            <li class="nav-item mx-0 mb-3" id="tab1">
                                <a class="nav-link active text-center step  step-1" data-toggle="tab" href="#tab-1" role="tab">Product-1</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-center bg-success text-white" id="add-new-tab" style="cursor: pointer;"><i class="fas fa-plus-circle mr-2 text-white"></i> Add More</a>
                            </li>
                        </ul>
                        
                            <input type="hidden" name="tab" id="check_tab">
                            <div class="tab-content">
                                <div class="tab-pane active step step-1 p-3" id="tab-1" role="tabpanel">
                                    <div class="row" id="production_1">
                                        <div class="col-md-12 px-0" style="border-top: 5px solid #024c96;">
                                            <div class="card card-custom card-fit card-border" style="border-radius: 0 !important;">
                                                <div class="card-body py-5">
                                                    <div class="row">
                                                        <div class="form-group col-md-12 required">
                                                            <label >Product</label>
                                                            <select name="production[1][product_id]" id="production_1_product_id"  onchange="materialData(this.value,1)" class="form-control selectpicker">
                                                                <option value="">Select Please</option>
                                                                @if (!$products->isEmpty())
                                                                    @foreach ($products as $id => $name)
                                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-3 required">
                                                            <label >Total Year</label>
                                                            <select name="production[1][year]" id="production_1_year"  onchange="generateDate(this.value,1)" class="form-control selectpicker">
                                                                @for ($i = 1; $i <= 3; $i++)
                                                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-3 required">
                                                            <label for="mfg_date">Mfg. Date</label>
                                                            <input type="text" class="form-control date" name="production[1][mfg_date]" id="production_1_mfg_date" value="{{ date('Y-m-d') }}" readonly />
                                                        </div>
                                                        <div class="form-group col-md-3 required">
                                                            <label for="exp_date">Exp. Date</label>
                                                            <input type="text" class="form-control date" name="production[1][exp_date]" id="production_1_exp_date" value="{{ date('Y-m-d',strtotime('+1 year')) }}" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 py-5">
                                            <div class="row" id="production_materials_1">

                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('production::production.view-modal')
@endsection

@push('scripts')
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    var tabcount = 1;
    function add_new_tab(tab){

        tab_btn_html = `<li class="nav-item mx-0 mb-3" id="tab`+tab+`">
                            <a class="nav-link text-center step  step-`+tab+`" data-toggle="tab" href="#tab-`+tab+`" role="tab">Product-`+tab+` <i data-tab="`+tab+`" class="fas fa-times-circle text-danger remove-tab ml-5 bg-white"></i></a>
                        </li>`;
        $('#form-tab li:last').before(tab_btn_html);

        tab_content_html = `<div class="tab-pane  step step-`+tab+`  p-3" id="tab-`+tab+`" role="tabpanel">
                                <div class="row"  id="production_`+tab+`">
                                    <div class="col-md-12 px-0" style="border-top: 5px solid #024c96;">
                                        <div class="card card-custom card-fit card-border" style="border-radius: 0 !important;">
                                            <div class="card-body py-5">
                                                <div class="row">
                                                    <div class="form-group col-md-12 required">
                                                        <label >Product</label>
                                                        <select name="production[`+tab+`][product_id]" id="production_`+tab+`_product_id"  onchange="materialData(this.value,`+tab+`)" class="form-control selectpicker">
                                                            <option value="">Select Please</option>
                                                            @if (!$products->isEmpty())
                                                                @foreach ($products as $id => $name)
                                                                <option value="{{ $id }}">{{ $name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-3 required">
                                                        <label >Total Year</label>
                                                        <select name="production[`+tab+`][year]" id="production_`+tab+`_year"  onchange="generateDate(this.value,`+tab+`)" class="form-control selectpicker">
                                                            @for ($i = 1; $i <= 3; $i++)
                                                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-3 required">
                                                        <label for="mfg_date">Mfg. Date</label>
                                                        <input type="text" class="form-control date" name="production[`+tab+`][mfg_date]" id="production_`+tab+`_mfg_date" value="{{ date('Y-m-d') }}" readonly />
                                                    </div>
                                                    <div class="form-group col-md-3 required">
                                                        <label for="exp_date">Exp. Date</label>
                                                        <input type="text" class="form-control date" name="production[`+tab+`][exp_date]" id="production_`+tab+`_exp_date" value="{{ date('Y-m-d',strtotime('+1 year')) }}" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 py-5">
                                        <div class="row" id="production_materials_`+tab+`">

                                        </div>
                                    </div>   
                                </div>
                            </div>`
        $('.tab-content').append(tab_content_html);
        $('.selectpicker').selectpicker('refresh');
        $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    }

    $(document).on('click','#add-new-tab',function(){
        tabcount++;
        add_new_tab(tabcount);
    });
    $(document).on('click','.remove-tab',function(){
        var tab = $(this).data('tab');
        Swal.fire({
            title: 'Are you sure to delete Tab-' + tab + '?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.value) {
                
                if($('#form-tab li#tab'+tab).is(':nth-last-child(2)'))
                {
                    tabcount--;
                }
                $('#tab'+tab+',.tab-pane#tab-'+tab).remove();
                $('#tab'+(tab-1)+' a, .tab-pane#tab-'+(tab-1)).addClass('active');
                Swal.fire("Removed", "Tab Removed Successfully", "success");
            }
        });
    });

});


function materialData(product_id,tab)
{
    $.ajax({
        url:"{{ url('production/product-materials') }}",
        data:{product_id:product_id,tab:tab,_token:_token},
        type:"POST",
        success:function(data){
            $('#production_materials_'+tab).empty().html(data);
        },
    });
}
function calculateRowTotal(tab,row)
{
    var cost = parseFloat($('#production_'+tab+'_materials_'+row+'_cost').val());
    var qty = parseFloat($('#production_'+tab+'_materials_'+row+'_qty').val());
    var stock_qty = parseFloat($('#production_'+tab+'_materials_'+row+'_stock_qty').val());
    var total  = 0;
    if(cost > 0 && qty > 0)
    {
        if(qty > stock_qty){
            $('#production_'+tab+'_materials_'+row+'_qty').val(1);
            $('#production_'+tab+'_materials_'+row+'_total').val(parseFloat(cost).toFixed(2));
            notification('error','Quantity must be less than or equal to stock quantity!');
        }else{
            total = parseFloat(cost * qty).toFixed(2);
            $('#production_'+tab+'_materials_'+row+'_total').val(total);
        }
    }else{
        $('#production_'+tab+'_materials_'+row+'_total').val('');
    }
}

function generateDate(number,tab)
{
    var mfg_date = $('#production_'+tab+'_mfg_date').val();
    var exp_date = new Date(new Date(mfg_date).setFullYear(new Date(mfg_date).getFullYear() + parseInt(number)));
    $('#production_'+tab+'_exp_date').val(exp_date.toISOString().slice(0, 10));
}


function check_material_stock()
{
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('production/check-material-stock')}}";
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
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
                console.log(data);
                if (data.status == 'success') {
                    store_data();
                }else{
                    $('#view_modal #view-data').empty().html(data);
                    $('#view_modal').modal({
                        keyboard: false,
                        backdrop: 'static',
                    });
                    $('#view_modal .modal-title').html('<i class="fas fa-file-alt text-white"></i> <span> Material Stock Availibility Details</span>');
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('production/store')}}";
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
            // if (data.status == false) {
            //     $.each(data.errors, function (key, value){
            //         var key = key.split('.').join('_');
            //         $('#store_or_update_form input#' + key).addClass('is-invalid');
            //         $('#store_or_update_form textarea#' + key).addClass('is-invalid');
            //         $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
            //         $('#store_or_update_form #' + key).parent().append(
            //         '<small class="error text-danger">' + value + '</small>');
            //     });
            // } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('production') }}");
                }
            // }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
</script>
@endpush