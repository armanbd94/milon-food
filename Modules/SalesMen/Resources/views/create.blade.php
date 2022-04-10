@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    .customer.table td{
        vertical-align: top !important;
        padding: 0 !important;
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
                    <a href="{{ route('sale') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <form action="" id="store_or_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="update_id" id="update_id" >
                            
                            <div class="col-md-9">
                                <div class="row">
                                    <input type="hidden" name="update_id" id="update_id" value="{{ isset($salesman) ? $salesman->id : '' }}" />
                                    <x-form.textbox labelName="Name" name="name" value="{{ isset($salesman) ? $salesman->name : '' }}" required="required" col="col-md-4" placeholder="Enter name"/>
                                    <x-form.textbox labelName="Username" name="username" value="{{ isset($salesman) ? $salesman->username : '' }}" required="required" col="col-md-4" placeholder="Enter username"/>
                                    <x-form.textbox labelName="Phone No." name="phone" value="{{ isset($salesman) ? $salesman->phone : '' }}" required="required" col="col-md-4" placeholder="Enter phone number"/>
                                    <x-form.textbox labelName="Email" name="email" value="{{ isset($salesman) ? $salesman->email : '' }}" col="col-md-4" placeholder="Enter email"/>
        
                                    <div class="col-md-4 form-group {{ isset($salesman) ? '' : 'required' }}">
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
                        
                                    <div class="col-md-4 form-group {{ isset($salesman) ? '' : 'required' }}">
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
                                    @if(!isset($salesman))
                                    <x-form.textbox labelName="Previous Balance" name="previous_balance" col="col-md-4" class="text-right" placeholder="Previous balalnce"/>
                                    @endif
                                    <x-form.selectbox labelName="ASM" name="asm_id" required="required" col="col-md-4" class="selectpicker">
                                        @if (!$asms->isEmpty())
                                        @foreach ($asms as $asm)
                                            <option value="{{ $asm->id }}" {{ isset($salesman) ? ($salesman->asm_id == $asm->id ? 'selected' : '') : '' }}>{{ $asm->name.' - '.$asm->phone.' ['.$asm->warehouse->name.']' }}</option>
                                        @endforeach 
                                        @endif
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required" col="col-md-4" class="selectpicker" onchange="setDistrictData();getUpazilaList(2);">
                                        @if (!$warehouses->isEmpty())
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}"  {{ isset($salesman) ? ($salesman->warehouse_id == $warehouse->id ? 'selected' : '') : '' }} data-districtid="{{ $warehouse->district_id }}" data-districtname="{{ $warehouse->district->name }}">{{ $warehouse->name }}</option>
                                        @endforeach 
                                        @endif
                                    </x-form.selectbox>
                                    <div class="form-group col-md-4 required">
                                        <label for="district_name">District</label>
                                        <input type="text" class="form-control" name="district_name" id="district_name"  value="{{ isset($salesman) ? $salesman->district->name : '' }}">
                                        <input type="hidden" class="form-control" name="district_id" id="district_id"  value="{{ isset($salesman) ? $salesman->district_id : '' }}">
                                    </div>
                                    <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-4" required="required" class="selectpicker"/>
                                    <div class="col-md-12">
                                        <table class="table table-bordered" id="area-table">
                                            <thead class="bg-primary">
                                                <th>Area</th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                                @if (isset($salesman) && !$salesman->areas->isEmpty())
                                                    @php $areas = DB::table('locations')->where('parent_id',$salesman->upazila_id)->get(); @endphp
                                                    @foreach ($salesman->areas as $key => $item)
                                                        <tr>
                                                            <td>
                                                                <select name="areas[{{ $key+1 }}][id]" id="areas_{{ $key+1 }}_id" class="selectpicker col-md-12 form-control" data-live-search="true" data-row="{{ $key+1 }}">
                                                                    <option value="">Select Please</option>
                                                                    @foreach ($areas as $area)
                                                                        <option value="{{ $area->id }}" {{ $item->pivot->area_id == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="text-center"><button type="button" class="btn btn-danger btn-md remove-area"><i class="fas fa-trash"></i></button></td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr><td><button type="button" class="btn btn-success btn-md add-area"><i class="fas fa-plus"></i></button></td></tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group col-md-12 mb-0">
                                    <label for="logo" class="form-control-label">Person Image</label>
                                    <div class="col=md-12 px-0  text-center">
                                        <div id="avatar">
                        
                                        </div>
                                    </div>
                                    <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                                    <input type="hidden" name="old_avatar" id="old_avatar">
                                </div>
                            </div>

                            <div class="form-group col-md-12 pt-5">
                                @if(!isset($salesman))
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn1" onclick="store_data(1)"><i class="fas fa-save"></i> Save</button>
                                <button type="button" class="btn btn-success btn-sm mr-3" id="save-btn2" onclick="store_data(2)"><i class="fas fa-save"></i> Save & Add New</button>
                                @else
                                <a href="{{ url('sales-representative') }}" class="btn btn-danger btn-sm mr-3"><i class="fas fa-window-close"></i> Cancel</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn1" onclick="store_data(1)"><i class="fas fa-save"></i> Update</button>
                                @endif 
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
<script src="js/spartan-multi-image-picker.min.js"></script>
<script>    
$(document).ready(function () {
    
    $("#avatar").spartanMultiImagePicker({
        fieldName:        'avatar',
        maxCount: 1,
        rowHeight:        '200px',
        groupClassName:   'col-md-12 col-sm-12 col-xs-12',
        maxFileSize:      '',
        dropFileLabel : "Drop Here",
        allowedExt: 'png|jpg|jpeg',
        onExtensionErr : function(index, file){
            Swal.fire({icon: 'error',title: 'Oops...',text: 'Only png,jpg,jpeg file format allowed!'});
        },

    });

    $("input[name='avatar']").prop('required',true);

    $('.remove-files').on('click', function(){
        $(this).parents(".col-md-12").remove();
    });
    @if(isset($salesman) && !empty($salesman->avatar))
    $('#avatar img').css('display','none');
    $('#avatar .spartan_remove_row').css('display','block');
    $('#avatar .img_').css('display','block');
    $('#avatar .img_').attr('src',"{{ asset('storage/'.SALESMEN_AVATAR_PATH.$salesman->avatar)}}");
    @else   
    $('#avatar img').css('display','block');
    $('#avatar .spartan_remove_row').css('display','none');
    $('#avatar .img_').css('display','none');
    $('#avatar .img_').attr('src','');
    @endif
    //Password Show/Hide
    $(".toggle-password").click(function () {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });
    var count = 1;
    @if (isset($salesman) && !$salesman->areas->isEmpty())
    count = "{{ count($salesman->areas) + 1 }}";
    @endif
    $('.add-area').click(function(){
        const upazila_id = document.getElementById('upazila_id').value;
        if(upazila_id)
        {
            $.ajax({
                url:"{{ url('upazila-id-wise-area-list') }}/"+upazila_id,
                type:"GET",
                dataType:"JSON",
                data:{upazila_id:upazila_id,_token:_token},
                success:function(data){
                    var area = `<option value="">Select Please</option>`;
                    $.each(data, function(key, value) {
                        area += '<option value="'+ key +'">'+ value +'</option>';
                    });
                    var html = `<tr>
                            <td>
                                <select name="areas[${count}][id]" id="areas_${count}_id" class="selectpicker col-md-12 form-control" data-live-search="true" data-row="${count}">
                                ${area}
                                </select> 
                            </td>
                            <td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-md remove-area"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                    $('#area-table tbody').append(html);
                    $('#area-table .selectpicker').selectpicker();
                    count++;
                },
            });
        }else{
            notification('error','Please select upazila');
        }
    });
    $('#area-table').on('click','.remove-area',function(){
        $(this).closest('tr').remove();
    });
});


function setDistrictData()
{
    $('#store_or_update_form #district_id').val($('#store_or_update_form #warehouse_id option:selected').data('districtid'))
    $('#store_or_update_form #district_name').val($('#store_or_update_form #warehouse_id option:selected').data('districtname'))
}
@if(isset($salesman))
getUpazilaList("{{ $salesman->upazila_id }}");
@endif
function getUpazilaList(upazila_id='')
{
    var district_id = $('#store_or_update_form #warehouse_id option:selected').data('districtid');
    if(district_id){
        $.ajax({
            url:"{{ url('district-id-wise-upazila-list') }}/"+district_id,
            type:"GET",
            dataType:"JSON",
            success:function(data){
                html = `<option value="">Select Please</option>`;
                $.each(data, function(key, value) {
                    html += '<option value="'+ key +'">'+ value +'</option>';
                });

                $('#store_or_update_form #upazila_id').empty().append(html);

                $('.selectpicker').selectpicker('refresh');
                if(upazila_id){
                    $('#store_or_update_form #upazila_id').val(upazila_id);
                    $('#store_or_update_form #upazila_id.selectpicker').selectpicker('refresh');
                }
                
            },
        });
    }
    
}



function store_data(type){
    var rownumber = $('table#area-table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert area to table!")
    }else{
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        let url = "{{route('sales.representative.store.or.update')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#save-btn'+type).addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#save-btn'+type).removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
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
                        if(type == 1)
                        {
                            window.location.replace("{{ route('sales.representative') }}");
                        }else{
                            window.location.replace("{{ route('sales.representative.add') }}");
                        }
                        
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
    
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
    const length    = 10;
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