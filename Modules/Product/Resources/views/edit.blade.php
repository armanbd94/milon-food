@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/tagify.css" rel="stylesheet" type="text/css" />
<style>
    .tagsinput{
        height: calc(1.5em + 1.3rem + 2px) !important;
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
                    <a href="{{ route('product') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-10">
                            <div class="row">
                                <input type="hidden" name="update_id" id="update_id" value="{{ $product->id }}">
        
                                <x-form.textbox labelName="Product Name" name="name" required="required" value="{{ $product->name }}" col="col-md-4" placeholder="Enter product name"/>
                                
                                <div class="col-md-4 form-group required">
                                    <label for="code">Code</label>
                                    <div class="input-group" id="code_section">
                                        <input type="text" class="form-control" name="code" id="code" value="{{ $product->code }}">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" id="generate-code"  data-toggle="tooltip" data-theme="dark" title="Generate Code"
                                            style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;cursor: pointer;">
                                                <i class="fas fa-retweet text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <x-form.selectbox labelName="Barcode Symbol" name="barcode_symbology" required="required" col="col-md-4" class="selectpicker">
                                    @foreach (BARCODE_SYMBOL as $key => $value)
                                        <option value="{{ $key }}" {{ ($key == $product->barcode_symbology) ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </x-form.selectbox> 


                                <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-4" class="selectpicker">
                                    @if (!$categories->isEmpty())
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"  {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                
                                <div class="form-group col-md-4 required">
                                    <label for="base_unit_id">Unit</label>
                                    <select name="base_unit_id" id="base_unit_id"  onchange="populate_unit(this.value,1)" class="form-control selectpicker" data-live-search="true"  data-live-search-placeholder="Search">
                                        <option value="">Select Please</option>
                                        @if (!$units->isEmpty())
                                            @foreach ($units as $unit)
                                                @if ($unit->base_unit == null)
                                                <option value="{{ $unit->id }}" {{ $product->base_unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
        
                                {{-- <div class="form-group col-md-4 required">
                                    <label for="unit_id">Unit</label>
                                    <select name="unit_id" id="unit_id"  class="form-control selectpicker" data-live-search="true"  data-live-search-placeholder="Search">
                                        <option value="">Select Please</option>
                                    @php
                                        $sale_units = \DB::table('units')->where('base_unit',$product->unit_id)
                                        ->orWhere('id',$product->unit_id)->get();
                                        
                                    @endphp
                                    @if (!$sale_units->isEmpty())
                                        @foreach ($sale_units as $unit)
                                        <option value="{{ $unit->id }}" {{ ($product->unit_id == $unit->id) ? 'selected' : '' }}>{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                        @endforeach
                                    @endif
                                    </select>
                                </div> --}}
                                
                                {{-- <x-form.textbox labelName="Unit Price" name="unit_price" value="{{ $product->unit_price }}" required="required" col="col-md-4 price" placeholder="Enter product price"/> --}}
                                <x-form.textbox labelName="Price" name="base_unit_price" value="{{ $product->base_unit_price }}" required="required" col="col-md-4 price" placeholder="Enter product price"/>
                                <x-form.textbox labelName="Alert Quantity" name="alert_quantity" value="{{ $product->alert_quantity }}"  col="col-md-4 alert-qty" placeholder="Enter product alert qty"/>
                                

                                <div class="col-md-4 form-group">
                                    <label for="tax_id">Product Tax</label>
                                    <select name="tax_id" id="tax_id" required="required" class="form-control selectpicker">
                                        <option value="0" selected>No Tax</option>
                                        @if (!$taxes->isEmpty())
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->id }}"  {{ $product->tax_id == $tax->id ? 'selected' : '' }}>{{ $tax->name }}</option>
                                            @endforeach 
                                        @endif
                                    </select>
                                </div>
        
                                <div class="col-md-4 form-group">
                                    <label for="tax_method">Tax Method<span class="text-danger">*</span> <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                        data-theme="dark" title="Exclusive: Poduct price = Actual product price + Tax. Inclusive: Actual product price = Product price - Tax"></i></label>
                                    <select name="tax_method" id="tax_method" class="form-control selectpicker">
                                    @foreach (TAX_METHOD as $key => $value)
                                        <option value="{{ $key }}" {{ $product->tax_method == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="row">
                                <div class="form-group col-md-12 mb-0 text-center">
                                    <label for="logo" class="form-control-label">Product Image</label>
                                    <div class="col=md-12 px-0  text-center">
                                        <div id="image">
                        
                                        </div>
                                    </div>
                                    <input type="hidden" name="old_image" id="old_image" value="{{ $product->image }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="form-group col-md-12 mt-5">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" id="description">{{ $product->description }}</textarea>
                        </div>

                        <div class="col-md-12 pt-5" id="material-section">
                            <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                    position: absolute;top:-16px;left:10px;">Materials</div>
                                <div class="col-md-12 pt-5 material_section">
                                @if (!$product->product_material->isEmpty())
                                    @foreach ($product->product_material as $key => $value)
                                    
                                        <div class="row {{ ($key == 0) ? '' : 'row_remove' }}">
                                            <div class="form-group col-md-5 required">
                                                @if($key == 0) <label for="materials_{{ $key+1 }}_id" class="form-control-label">Material Name</label> @endif
                                                <select name="materials[{{ $key+1 }}][id]" id="materials_{{ $key+1 }}_id" required="required" class="form-control selectpicker" data-live-search="true" 
                                                data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if (!$materials->isEmpty())
                                                        @foreach ($materials as $material)
                                                            <option value="{{ $material->id }}" {{ ($value->id == $material->id) ? 'selected' : '' }}>{{ $material->material_name }}</option>
                                                        @endforeach 
                                                    @endif
                                                </select>
                                            </div>
                                            @if ($key == 0)
                                            <div class="form-group col-md-2" style="padding-top: 28px;">
                                                <button type="button" id="add-material" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Add More">
                                                    <i class="fas fa-plus-square"></i>
                                                    </button>
                                            </div>
                                            @else
                                            <div class="form-group col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Remove">
                                                    <i class="fas fa-minus-square"></i>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    
                                    @endforeach
                                @endif 
                                </div>   
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12 pt-5">
                            <button type="button" class="btn btn-primary btn-sm" id="update-btn">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/spartan-multi-image-picker.min.js"></script>
<script>
$(document).ready(function () {

    /** Start :: Product Image **/
    $("#image").spartanMultiImagePicker({
        fieldName:        'image',
        maxCount: 1,
        rowHeight:        '150px',
        groupClassName:   'col-md-12 col-sm-12 col-xs-12',
        maxFileSize:      '',
        dropFileLabel : "Drop Here",
        allowedExt: '',
    });

    $("input[name='image']").prop('required',true);

    $('.remove-files').on('click', function(){
        $(this).parents(".col-md-12").remove();
    });

    @if(!empty($product->image))
    $('#image img').css('display','none');
    $('#image .spartan_remove_row').css('display','block');
    $('#image .img_').css('display','block');
    $('#image .img_').attr('src',"{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}");
    @else   
    $('#image img').css('display','block');
    $('#image .spartan_remove_row').css('display','none');
    $('#image .img_').css('display','none');
    $('#image .img_').attr('src','');
    @endif
    /** End :: Product Image **/


    /** Start :: Add More Material Field **/
    @if(!$product->product_material->isEmpty())
    var material_count = "{{ count($product->product_material) }}";
    @else
    var material_count = 1;
    @endif
    function add_more_material_field(row){
        html = ` <div class="row row_remove">
                    <div class="form-group col-md-5 required">
                        <select name="materials[`+row+`][id]" id="materials_`+row+`_id" required="required" class="form-control selectpicker">
                            <option value="">Select Please</option>
                            @if (!$materials->isEmpty())
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}">{{ $material->material_name }}</option>
                                @endforeach 
                            @endif
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                            data-placement="top" data-original-title="Remove">
                            <i class="fas fa-minus-square"></i>
                        </button>
                    </div>
                </div>`;
        $('.material_section').append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add-material',function(){
        material_count++;
        add_more_material_field(material_count);
    });
    $(document).on('click','.remove',function(){
        material_count--;
        $(this).closest('.row_remove').remove();
    });

    
    //Generate Code
    $(document).on('click','#generate-code',function(){
        $.ajax({
            url: "{{ route('product.generate.code') }}",
            type: "GET",
            dataType: "JSON",
            beforeSend: function(){
                $('#generate-code').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#generate-code').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                data ? $('#store_or_update_form #code').val(data) : $('#store_or_update_form #code').val('');
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });

    /****************************/
    $(document).on('click','#update-btn',function(){
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);

        $.ajax({
            url: "{{route('product.store.or.update')}}",
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#update-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#update-btn').removeClass('spinner spinner-white spinner-right');
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
                        if(key == 'code'){
                            $('#store_or_update_form #' + key).parents('.form-group').append(
                            '<small class="error text-danger">' + value + '</small>');
                        }else{
                            $('#store_or_update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                        }
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                            window.location.replace("{{ route('product') }}");
                    }
                }
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
        });
});

function populate_unit(unit_id)
{
    $.ajax({
        url:"{{ url('populate-unit') }}/"+unit_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            $('#purchase_unit_id,#sale_unit_id').empty();
            $.each(data, function(key, value) {
                $('#purchase_unit_id,#sale_unit_id').append('<option value="'+ key +'">'+ value +'</option>');
            });
            $('#purchase_unit_id.selectpicker,#sale_unit_id.selectpicker').selectpicker('refresh');
        },
    });
}
</script>
@endpush