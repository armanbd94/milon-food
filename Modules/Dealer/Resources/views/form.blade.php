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
                                <input type="hidden" name="dealer_id" id="dealer_id" value="{{ isset($dealer) ? $dealer->id : '' }}">
                                <x-form.textbox labelName="Dealer Name" name="name" value="{{ isset($dealer) ? $dealer->name : '' }}" required="required" col="col-md-4" placeholder="Enter customer name"/>
                                <x-form.textbox labelName="Shop Name" name="shop_name" value="{{ isset($dealer) ? $dealer->shop_name : '' }}" col="col-md-4" placeholder="Enter shop name"/>
                                <x-form.textbox labelName="Mobile" name="mobile" value="{{ isset($dealer) ? $dealer->mobile : '' }}" col="col-md-4" required="required" placeholder="Enter mobile number"/>
                                <x-form.textbox labelName="Username" name="username" value="{{ isset($dealer) ? $dealer->username : '' }}" required="required" col="col-md-4" placeholder="Enter username"/>
                                <div class="col-md-4 form-group">
                                    <label for="site_title">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control bg-brand" name="password" id="password">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning" id="generate_password" style="border:0;cursor: pointer;" data-toggle="tooltip" data-theme="dark" title="Generate Password">
                                                <i class="fas fa-lock text-white"></i>
                                            </span>
                                        </div>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;">
                                                <i class="fas fa-eye toggle-password text-white" toggle="#password" style="cursor: pointer;"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="col-md-4 form-group">
                                    <label for="site_title">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control bg-brand" name="password_confirmation" id="password_confirmation">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;">
                                                <i class="fas fa-eye toggle-password text-white" toggle="#password_confirmation" style="cursor: pointer;"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <x-form.textbox labelName="Email" name="email" value="{{ isset($dealer) ? $dealer->email : '' }}" type="email" col="col-md-4" placeholder="Enter email address"/>
                                @if(Auth::user()->warehouse_id)
                                <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ Auth::user()->warehouse_id }}">
                                @else
                                <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required" col="col-md-4" class="selectpicker">
                                    @if (!$warehouses->isEmpty())
                                        @foreach ($warehouses as $id => $name)
                                            <option value="{{ $id }}" {{ isset($dealer) ? ( $dealer->warehouse_id == $id ? 'selected' : '') : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                @endif
                                <x-form.selectbox labelName="District" name="district_id" required="required" onchange="getUpazilaList(this.value)" col="col-md-4" class="selectpicker">
                                    @if (!$districts->isEmpty())
                                        @foreach ($districts as $id => $name)
                                            <option value="{{ $id }}" {{ isset($dealer) ? ( $dealer->district_id == $id ? 'selected' : '') : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-4" required="required" class="selectpicker" />
                                @if(!isset($dealer))
                                <x-form.textbox labelName="Previous Balance" name="previous_balance" col="col-md-4 pbalance" placeholder="Previous credit balalnce"/>
                                @endif
                                <x-form.textbox labelName="Dealer Address" name="address" value="{{ isset($dealer) ? $dealer->address : '' }}" col="col-md-4" required="required" placeholder="Enter dealer address"/>
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
                                    <input type="hidden" name="old_avatar" id="old_avatar" value="{{ isset($dealer) ? $dealer->avatar : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 pt-5" id="product-section">
                            <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                    position: absolute;top:-16px;left:45%;">Products</div>
                                <div class="col-md-12 pt-5 ">
                                    <table class="table table-bordered" id="product_section">
                                        <thead class="bg-primary">
                                            <th>Product</th>
                                            <th class="text-center">Unit</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Commission Rate</th>
                                            <th class="text-center">Commission Percentage</th>
                                            <th class="text-center">Action</th>
                                        </thead>
                                        <tbody>
                                        @if (isset($dealer) && !$dealer->hasManyProducts->isEmpty())
                                            @foreach ($dealer->hasManyProducts as $key => $item)
                                            <tr>
                                                <td>
                                                    <select name="products[{{ $key+1 }}][id]" id="products_{{ $key+1 }}_id" required="required" onchange="setProductData({{ $key+1 }})" class="form-control selectpicker" data-live-search="true" 
                                                    data-live-search-placeholder="Search">
                                                        <option value="">Select Please</option>
                                                        @if (!$products->isEmpty())
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : ''  }} data-price="{{ $product->base_unit_price }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name }}</option>
                                                            @endforeach 
                                                        @endif
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="products[{{ $key+1 }}][unit]" id="products_{{ $key+1 }}_unit" value="{{ $item->product->unit->unit_name }}" class="form-control text-center bg-secondary" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="products[{{ $key+1 }}][price]" id="products_{{ $key+1 }}_price" value="{{ $item->product->base_unit_price }}" class="form-control text-right bg-secondary" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="products[{{ $key+1 }}][commission_rate]" class="form-control text-right"  value="{{ $item->commission_rate }}" onkeyup="generateCommissionPercentage({{ $key+1 }})" id="products_{{ $key+1 }}_commission_rate" >
                                                </td>
                                                <td>
                                                    <input type="text" name="products[{{ $key+1 }}][commission_percentage]" value="{{ $item->product->base_unit_price > 0 ? number_format($item->commission_rate/$item->product->base_unit_price,4,'.',',') : 0 }}" class="form-control text-center bg-secondary" id="products_{{ $key+1 }}_commission_percentage" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Remove">
                                                    <i class="fas fa-minus-square"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr>
                                            <td>
                                                <select name="products[1][id]" id="products_1_id" required="required" onchange="setProductData(1)" class="form-control selectpicker" data-live-search="true" 
                                                data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if (!$products->isEmpty())
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->base_unit_price }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name }}</option>
                                                        @endforeach 
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="products[1][unit]" id="products_1_unit" class="form-control text-center bg-secondary" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="products[1][price]" id="products_1_price" class="form-control text-right bg-secondary" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="products[1][commission_rate]" class="form-control text-right" onkeyup="generateCommissionPercentage(1)" id="products_1_commission_rate" >
                                            </td>
                                            <td>
                                                <input type="text" name="products[1][commission_percentage]" class="form-control text-center bg-secondary" id="products_1_commission_percentage" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                        @endif
                                            
                                        </tbody>
                                        <tfoot>
                                            <thead class="bg-primary">
                                                <th colspan="5"></th>
                                                <th class="text-center">
                                                    <button type="button" id="add-product" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Add More">
                                                    <i class="fas fa-plus-square"></i>
                                                    </button>
                                                </th>
                                            </thead>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 pt-5 text-center">
                            @if(!isset($dealer))
                            <button type="button" class="btn btn-primary btn-sm" id="save-btn-1" onclick="storeData(1)"><i class="fas fa-save"></i> Save</button>
                            <button type="button" class="btn btn-success btn-sm ml-3" id="save-btn-2" onclick="storeData(2)"><i class="fas fa-save"></i> Save & Add Another</button>
                            @else 
                            <a href="{{ url('dealer') }}" type="button" class="btn btn-danger btn-sm mr-2"><i class="fas fa-window-close"></i> Cancle</a>
                            <button type="button" class="btn btn-primary btn-sm" id="save-btn-1" onclick="storeData(1)"><i class="fas fa-save"></i> Update</button>
                            @endif
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
$("#kt_body").addClass("aside-minimize");
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
    @if(isset($dealer) && !empty($dealer->avatar))
    $('#avatar img').css('display','none');
    $('#avatar .spartan_remove_row').css('display','block');
    $('#avatar .img_').css('display','block');
    $('#avatar .img_').attr('src',"{{ asset('storage/'.DEALER_AVATAR_PATH.$dealer->avatar)}}");
    @else   
    $('#avatar img').css('display','block');
    $('#avatar .spartan_remove_row').css('display','none');
    $('#avatar .img_').css('display','none');
    $('#avatar .img_').attr('src','');
    @endif
    /** End ::Dealer Photo **/


    /** Start :: Add Dealer Multiple Product Field **/

    var product_count = 1;
    @if (isset($dealer) && !$dealer->hasManyProducts->isEmpty())
    product_count = "{{ count($dealer->hasManyProducts) }}";
    @endif
    function add_more_product_field(row){
        html = ` <tr>
                    <td>
                        <select name="products[${row}][id]" id="products_${row}_id" required="required" onchange="setProductData(${row})" class="form-control selectpicker" data-live-search="true" 
                        data-live-search-placeholder="Search">
                            <option value="">Select Please</option>
                            @if (!$products->isEmpty())
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->base_unit_price }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name }}</option>
                                @endforeach 
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" name="products[${row}][unit]" id="products_${row}_unit" class="form-control  text-center bg-secondary" readonly>
                    </td>
                    <td>
                        <input type="text" name="products[${row}][price]" id="products_${row}_price" class="form-control text-right bg-secondary" readonly>
                    </td>
                    <td>
                        <input type="text" name="products[${row}][commission_rate]" class="form-control text-right" id="products_${row}_commission_rate" onkeyup="generateCommissionPercentage(${row})">
                    </td>
                    <td>
                        <input type="text" name="products[${row}][commission_percentage]" class="form-control text-center bg-secondary" id="products_${row}_commission_percentage" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Remove">
                                                    <i class="fas fa-minus-square"></i>
                                                    </button>
                    </td>
                </tr>`;
        $('#product_section tbody').append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add-product',function(){
        product_count++;
        add_more_product_field(product_count);
    });
    $(document).on('click','.remove',function(){
        product_count--;
        $(this).closest("tr").remove();
    });
    /** End :: Add More product Field **/
    $(".toggle-password").click(function () {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });
});
function setProductData(row)
{
    const unit = $(`#products_${row}_id option:selected`).data('unitname');
    const price = $(`#products_${row}_id option:selected`).data('price');
    console.log(unit,price);
    $(`#products_${row}_unit`).val(unit);
    $(`#products_${row}_price`).val(price);
    generateCommissionPercentage(row);
}
function generateCommissionPercentage(row)
{
    const price = $(`#products_${row}_price`).val() ? parseFloat($(`#products_${row}_price`).val()) : 0;
    const commission_rate = $(`#products_${row}_commission_rate`).val() ? parseFloat($(`#products_${row}_commission_rate`).val()) : 0;
    let commission_percentage = 0;
    if(price > 0 && commission_rate > 0){
        commission_percentage = commission_rate/price;
    }
    $(`#products_${row}_commission_percentage`).val(parseFloat(commission_percentage).toFixed(4));

    
}
@if (isset($dealer))
getUpazilaList("{{ $dealer->district_id }}","{{ $dealer->upazila_id }}");
@endif
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
        url: "{{route('dealer.store.or.update')}}",
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
                    if(key == 'password' || key == 'password_confirmation'){
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
                        window.location.replace("{{ route('dealer') }}");
                    }else{
                        window.location.replace("{{ route('dealer.add') }}");
                    }
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
/***************************************************
 * * * Begin :: Random Password Genrate Code * * *
 **************************************************/
 const randomFunc = {
    upper : getRandomUpperCase,
    lower : getRandomLowerCase,
    number : getRandomNumber,
    symbol : getRandomSymbol
};


function getRandomUpperCase(){
    return String.fromCharCode(Math.floor(Math.random()*26)+65);
}
function getRandomLowerCase(){
   return String.fromCharCode(Math.floor(Math.random()*26)+97);
}
function getRandomNumber(){
   return String.fromCharCode(Math.floor(Math.random()*10)+48);
}
function getRandomSymbol(){
    var symbol = "!@#$%^&*=~?";
    return symbol[Math.floor(Math.random()*symbol.length)];
}
//generate event
document.getElementById("generate_password").addEventListener('click', () =>{
    const length    = 8;
    const hasUpper  = true;
    const hasLower  = true;
    const hasNumber = true;
    const hasSymbol = true;
    let   password  = generatePassword(hasUpper, hasLower, hasNumber, hasSymbol, length);
    document.getElementById("password").value = password;
    document.getElementById("password_confirmation").value = password;
});
//Generate Password Function
function generatePassword(upper, lower, number, symbol, length){
    let generatedPassword = "";
    const typesCount = upper + lower + number + symbol;
    const typesArr = [{upper}, {lower}, {number}, {symbol}].filter(item => Object.values(item)[0]);
    if(typesCount === 0) {
        return '';
    }
    for(let i=0; i<length; i+=typesCount) {
        typesArr.forEach(type => {
            const funcName = Object.keys(type)[0];
            generatedPassword += randomFunc[funcName]();
        });
    }
    const finalPassword = generatedPassword.slice(0, length);
    return finalPassword;
}
/***************************************************
 * * * End :: Random Password Genrate Code * * *
 **************************************************/
</script>
@endpush