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
                   <a href="{{ route('production') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="card-body">
                    <div class="col-md-12 text-center">
                        <h5>
                            <b>Batch No.:</b> {{ $production->batch_no }} <br>
                            <b>Warehouse:</b> {{ $production->warehouse->name }} <br>
                            <b>Start Date:</b> {{ date('d-M-Y',strtotime($production->start_date)) }}
                            @if($production->end_date) <br><b>Start Date:</b> {{ date('d-M-Y',strtotime($production->end_date)) }} @endif
                        </h5>
                    </div>
                    <div class="col-md-12 pt-5">
                        @if (!$production->products->isEmpty())
                            @foreach ($production->products as $key => $item)
                            <div class="row pt-5">
                                <div class="col-md-12 text-center">
                                    <h3 class="py-3 bg-warning text-white" style="margin: 10px auto 10px auto;">{{ ($key+1).' - '.$item->product->name }}</h3>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered pb-5" id="material_table_{{ $key + 1 }}">
                                        <thead class="bg-primary">
                                            <th class="text-center">Mfg. Date</th>
                                            <th class="text-center">Exp. Date</th>
                                            <th class="text-center">Unit Name</th>
                                            <th class="text-center">Finish Goods Qty</th>
                                            @if ($item->has_coupon == 1)
                                            <th class="text-center">Total Coupon</th>
                                            <th class="text-center">Coupon Price</th>
                                            <th class="text-center">Coupon Exp. Date</th>
                                            @endif
                                            
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">{{ date('d-M-Y',strtotime($item->mfg_date)) }}</td>
                                                <td class="text-center">{{ date('d-M-Y',strtotime($item->exp_date)) }}</td>
                                                <td class="text-center">{{ $item->product->base_unit->unit_name.' ('.$item->product->base_unit->unit_code.')' }}</td>
                                                <td class="text-center">{{ $item->base_unit_qty }}</td>
                                                @if ($item->has_coupon == 1)
                                                <td class="text-center">{{ $item->total_coupon }}</td>
                                                <td class="text-center">{{ number_format($item->coupon_price,2,'.','') }}</td>
                                                <td class="text-center">{{ date('d-M-Y',strtotime($item->coupon_exp_date)) }}</td>
                                                @endif
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-bordered pb-5" id="material_table_{{ $key + 1 }}">
                                        <thead class="bg-primary">
                                            <th width="30%">Material</th>
                                            <th width="5%" class="text-center">Type</th>
                                            <th width="10%" class="text-center">Unit Name</th>
                                            <th width="10%" class="text-right">Rate</th>
                                            <th class="text-center">Received Qty</th>
                                            <th class="text-center">Used Qty</th>
                                            <th class="text-center">Damaged Qty</th>
                                            <th class="text-center">Odd Qty</th>
                                        </thead>
                                        <tbody>
                                            @if (!$item->materials->isEmpty())
                                                @foreach ($item->materials as $index => $value)
                                                    <tr>
                                                        <td> {{ $value->material_name .' ('.$value->material_code.')' }} </td>
                                                        <td class="text-center">{{ MATERIAL_TYPE[$value->type] }}</td>
                                                        <td class="text-center">{{ $value->unit->unit_name.' ('.$value->unit->unit_code.')' }}</td>
                                                        <td class="text-right">{{ number_format($value->pivot->cost,2,'.','') }} </td>
                                                        <td class="text-center"> {{ number_format($value->pivot->qty,2,'.','') }} </td>
                                                        <td class="text-center">{{ $value->pivot->used_qty }}</td>
                                                        <td class="text-center">{{ $value->pivot->damaged_qty }}</td>
                                                        <td class="text-center">{{ $value->pivot->odd_qty }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="6" class="text-right font-weight-bold">Total Cost</td>
                                                    <td colspan="2" class="text-right">
                                                        @php
                                                            if(!empty($item->per_unit_cost) && !empty($item->base_unit_qty))
                                                            {
                                                                $total_cost = $item->per_unit_cost * $item->base_unit_qty;
                                                            }else{
                                                                $total_cost = '';
                                                            }
                                                        @endphp
                                                        {{ $total_cost }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right font-weight-bold">Per Unit Cost</td>
                                                    <td colspan="2" class="text-right">{{ $item->per_unit_cost ? number_format($item->per_unit_cost,2,'.','') : '' }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        @endif
                        
                    </div>
                </div>
            </form>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection
