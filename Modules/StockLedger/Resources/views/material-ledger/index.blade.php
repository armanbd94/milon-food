@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    table#dataTable{
        width:2200px !important;
    }
    #dataTable ul li{
        border-bottom: 1px solid #EBEDF3;
        margin-bottom: 5px;
    }
    #dataTable ul li:last-child{
        border-bottom: 0;
        margin-bottom: 0;
    }

    #dataTable tbody tr td:nth-child(14),
    #dataTable tbody tr td:nth-child(15),
    #dataTable tbody tr td:nth-child(16){
        padding-left: 0px !important;
        padding-right: 0px !important;
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
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.selectbox labelName="Material" name="material_id" col="col-md-4" class="selectpicker">
                            @if (!$materials->isEmpty())
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}">{{ $material->material_name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>

                        <div class="form-group col-md-4">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="">
                                <input type="hidden" id="start_date" name="start_date" value="">
                                <input type="hidden" id="end_date" name="end_date" value="">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div style="margin-top:28px;">       
                                <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                data-toggle="tooltip" data-theme="dark" title="Reset">
                                <i class="fas fa-undo-alt"></i></button>

                                <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                data-toggle="tooltip" data-theme="dark" title="Search">
                                <i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row" style="position:relative;">
                        <div class="col-sm-12 table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th class="text-center">Date</th>
                                        <th>Material</th>
                                        <th class="text-center">Unit</th>
                                        
                                        
                                        <th class="text-right">Prev. Qty</th>
                                        @if(permission('material-stock-ledger-cost-view'))
                                        <th class="text-right">Prev. Rate</th>
                                        <th class="text-right">Prev. Value</th>
                                        @endif
                                        
                                        <th class="text-center">Purchase No.</th>
                                        <th class="text-right">Stock In Qty</th>
                                        @if(permission('material-stock-ledger-cost-view'))
                                        <th class="text-right">Stock In Rate</th>
                                        <th class="text-right">Stock In Value</th>
                                        @endif
                                        
                                        <th class="text-center">Batch No.</th>
                                        <th class="text-center">Return No.</th>
                                        <th class="text-center">Damage No.</th>
                                        <th class="text-right">Stock Out Qty</th>
                                        @if(permission('material-stock-ledger-cost-view'))
                                        <th class="text-right">Stock Out Rate</th>
                                        <th class="text-right">Stock Out Subtotal</th>
                                        <th class="text-right">Stock Out Value</th>
                                        @endif
                                       
                                        <th class="text-right">Current Qty</th>
                                        @if(permission('material-stock-ledger-cost-view'))
                                        <th class="text-right">Current Rate</th>
                                        <th class="text-right">Current Value</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="col-md-12 d-none" id="table-loader" style="position: absolute;top:80px;left:0;">
                            <div style="width: 120px;
                            height: 70px;
                            background: white;
                            text-align: center;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            margin: 0 auto;">
                                <i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/knockout-3.4.2.js"></script>
<script src="js/moment.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
    $('.daterangepicker-filed').daterangepicker({
        timeZone: 'Asia/Dhaka',
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date   = endDate.format('YYYY-MM-DD');
            var title = start_date + ' To ' + end_date;
            $(this).val(title);
            $('input[name="start_date"]').val(start_date);
            $('input[name="end_date"]').val(end_date);
        }
    });

    $(document).ready(function(){

    
        $('#btn-filter').click(function () {
            let start_date = $('#start_date').val();
            let end_date   = $('#end_date').val();
            let material_id = $('#material_id option:selected').val();
            if(material_id)
            {
                $.ajax({
                    url: "{{route('material.stock.ledger.data')}}",
                    type: "POST",
                    data: {start_date:start_date,end_date:end_date,material_id:material_id,_token:_token},
                    beforeSend: function(){
                        $('#table-loader').removeClass('d-none');
                    },
                    complete: function(){
                        $('#table-loader').addClass('d-none');
                    },
                    success:function(data){
                        if(data)
                        {
                            $('#dataTable tbody').html('');
                            $('#dataTable tbody').html(data);
                        }else{
                            $('#dataTable tbody').html('');
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }else{
                notification('error','Please select material');
            }
        });
    
        $('#btn-reset').click(function () {
            $('#form-filter')[0].reset();
            $('#form-filter .selectpicker').selectpicker('refresh');
            $('#form-filter #start_date').val('');
            $('#form-filter #end_date').val('');
            $('#dataTable tbody').html('');
        });
 
    });
    </script>
@endpush
