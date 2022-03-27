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
                    <a href="{{ route('dealer') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                                <input type="hidden" name="dealer_id" id="dealer_id">
                                <x-form.textbox labelName="Customer Name" name="name" required="required" col="col-md-6" placeholder="Enter customer name"/>
                                <x-form.textbox labelName="Shop Name" name="shop_name" col="col-md-6" required="required" placeholder="Enter shop name"/>
                                <x-form.textbox labelName="Mobile" name="mobile" col="col-md-6" required="required" placeholder="Enter mobile number"/>
                                <x-form.textbox labelName="Email" name="email" type="email" col="col-md-6" placeholder="Enter email address"/>
                                <x-form.selectbox labelName="District" name="district_id" required="required" onchange="getUpazilaList(this.value)" col="col-md-6" class="selectpicker">
                                    @if (!$locations->isEmpty())
                                        @foreach ($locations as $location)
                                            @if ($location->type == 1 && $location->parent_id == null)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                  </x-form.selectbox>
                                  <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-6" required="required" class="selectpicker" />
                                  <x-form.textbox labelName="Previous Balance" name="previous_balance" col="col-md-6 pbalance" placeholder="Previous credit balalnce"/>
                                  <x-form.textarea labelName="Customer Address" name="address" col="col-md-6" required="required" placeholder="Enter customer address"/>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="row">
                                <div class="form-group col-md-12 mb-0 text-center">
                                    <label for="logo" class="form-control-label">Dealer Photo</label>
                                    <div class="col=md-12 px-0  text-center">
                                        <div id="avatar">
                        
                                        </div>
                                    </div>
                                    <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                                    <input type="hidden" name="old_avatar" id="old_avatar">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 pt-5" id="product-section">
                            <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                    position: absolute;top:-16px;left:10px;">Products</div>
                                <div class="col-md-12 pt-5 product_section">
                                    <div class="row">
                                        <div class="form-group col-md-5 required">
                                            <label for="products_1_id" class="form-control-label">Product</label>
                                            <select name="products[1][id]" id="products_1_id" required="required" class="form-control selectpicker" data-live-search="true" 
                                            data-live-search-placeholder="Search">
                                                <option value="">Select Please</option>
                                                @if (!$products->isEmpty())
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name }}</option>
                                                    @endforeach 
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2" style="padding-top: 28px;">
                                            <button type="button" id="add-product" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                                data-placement="top" data-original-title="Add More">
                                                <i class="fas fa-plus-square"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 pt-5">
                            <button type="button" class="btn btn-primary btn-sm" id="save-btn-1" onclick="storeData(1)">Save</button>
                            <button type="button" class="btn btn-success btn-sm ml-3" id="save-btn-2" onclick="storeData(2)">Save & Add Another</button>
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

    /** Start ::Dealer Photo **/
    $("#avatar").spartanMultiImagePicker({
        fieldName:        'avatar',
        maxCount: 1,
        rowHeight:        '150px',
        groupClassName:   'col-md-12 col-sm-12 col-xs-12',
        maxFileSize:      '',
        dropFileLabel : "Drop Here",
        allowedExt: '',
        // onExtensionErr : function(index, file){
        //     Swal.fire({icon: 'error',title: 'Oops...',text: 'Only png,jpg,jpeg file format allowed!'});
        // },

    });

    $("input[name='avatar']").prop('required',true);

    $('.remove-files').on('click', function(){
        $(this).parents(".col-md-12").remove();
    });
    /** End ::Dealer Photo **/


    /** Start :: Add Dealer Multiple Product Field **/
    var product_count = 1;
    function add_more_product_field(row){
        html = ` <div class="row row_remove">
                    <div class="form-group col-md-5 required">
                        <select name="products[`+row+`][id]" id="products_`+row+`_id" required="required" class="form-control selectpicker">
                            <option value="">Select Please</option>
                            @if (!$products->isEmpty())
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
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
        $('.product_section').append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add-product',function(){
        product_count++;
        add_more_product_field(product_count);
    });
    $(document).on('click','.remove',function(){
        product_count--;
        $(this).closest('.row_remove').remove();
    });
    /** End :: Add More product Field **/

    


});
function getUpazilaList(district_id,upazila_id=''){
    $.ajax({
        url:"{{ url('district-id-wise-upazila-list') }}/"+district_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });

            $('#store_or_update_form #upazila_id').empty();
            $('#store_or_update_form #upazila_id').append(html);
            
            $('.selectpicker').selectpicker('refresh');
            if(upazila_id){
                $('#store_or_update_form #upazila_id').val(upazila_id);
                $('#store_or_update_form #upazila_id.selectpicker').selectpicker('refresh');
            }
      
        },
    });
}
function storeData(btn)
{
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
            $('#save-btn-'+btn).addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
            $('#save-btn-'+btn).removeClass('spinner spinner-white spinner-right');
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
                    if(btn == 1){
                        window.location.replace("{{ route('product') }}");
                    }else{
                        window.location.replace("{{ route('product.add') }}");
                    }
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