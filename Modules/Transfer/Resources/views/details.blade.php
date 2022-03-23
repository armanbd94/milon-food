@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<style>
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
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                    
                    <a href="{{ route('transfer.inventory') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body" style="padding-bottom: 100px !important;">
                <div class="col-md-12 col-lg-12"  style="width: 100%;">
                    <div id="invoice">
                        <x-invoice-style />
                        <div class="invoice overflow-auto">
                            <div>
                                <table width="100%" style="margin:0;padding:0;">
                                    <tr>
                                        <td width="100%" class="text-center">
                                            <h3 style="margin:0;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;"><b>{{ config('settings.address') }}</b></p>@endif
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }}
                                                 @if(config('settings.email'))<b>, Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                                 <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                                                            font-weight: bold;background: black;
                                                            border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Transfer Challan</p>
                                           
                                        </td>
                                    </tr>
                                </table>
                                <div style="width: 100%;height:3px;border-top:2px dashed #000;margin-bottom:5px;"></div>
                                <table id="info_table">
                                    <tr>
                                        <td width="50%">
                                            <table>
                                                <tr><td><b>Transfer Information</b></td></tr>
                                                <tr><td><b>Challan No.</b></td><td><b>: </b>{{ $transfer->challan_no }}</td></tr>
                                                <tr><td><b>Transfer Date</b></td><td><b>: </b>{{ date('d-M-Y',strtotime($transfer->transfer_date)) }}</td></tr>
                                                <tr><td><b>From Warehouse</b></td><td><b>: </b>{{ $transfer->from_warehouse->name }}</td></tr>
                                                <tr><td><b>To Warehouse</b></td><td><b>: </b>{{ $transfer->to_warehouse->name }}</td></tr>
                                                <tr><td><b>Transfer Status</b></td><td><b>: </b>{{ $transfer->transfer_status == 1 ? 'Transfered' : 'Pending' }}</td></tr>
                                            </table>
                                        </td>
                                        <td width="50%">
                                            <table>
                                                <tr><td></td></tr>
                                                <tr><td class="text-right"><b>Reference No.</b></td><td><b>: </b>{{ $transfer->reference_no }}</td></tr>
                                                <tr><td class="text-right"><b>Driver Name</b></td><td><b>: </b>{{ $transfer->driver_name }}</td></tr>
                                                <tr><td class="text-right"><b>Driver Mobile No.</b></td><td><b>: </b>{{ $transfer->dmobile_no }}</td></tr>
                                                <tr><td class="text-right"><b>Shipping Cost</b></td><td><b>: </b>{{ $transfer->shipping_cost }}</td></tr>
                                                <tr><td class="text-right"><b>Receive Status</b></td><td><b>: </b>@if ($transfer->receive_status == 1)
                                                    {{ 'Received' }}
                                                @elseif ($transfer->receive_status == 2)
                                                    {{ 'Partial' }}
                                                @else  
                                                    {{ 'Pending' }}
                                                @endif</td></tr>
                                                @if(empty(Auth::user()->warehouse_id))
                                                <tr><td class="text-right"><b>Receiver</b></td><td><b>: </b>{{ $transfer->receiver->name }}</td></tr>
                                                <tr><td class="text-right"><b>Receive Date</b></td><td><b>: </b>{{ $transfer->receive_date ? date('d-M-Y',strtotime($transfer->receive_date)) : '' }}</td></tr>
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <table  id='product_table'>
                                    <tbody>
                                        <tr>
                                            <td class="text-center"><b>SL.</b></td>
                                            <td class="text-left"><b>PRODUCT</b></td>
                                            <td class="text-center"><b>UNIT</b></td>
                                            <td class="text-center"><b>TRANSFER QTY</b></td>
                                            <td class="text-center"><b>RECEIVE QTY</b></td>
                                            <td class="text-center"><b>DAMAGE QTY</b></td>
                                            @if(empty(Auth::user()->warehouse_id))
                                            <td class="text-center"><b>COST</b></td>
                                            <td class="text-center"><b>VALUE</b></td>
                                            @endif
                                        </tr>
                                        @if (!$transfer->hasManyProducts->isEmpty())
                                            @php
                                                $total_receive_qty = $total_damage_qty = 0;
                                            @endphp
                                            @foreach ($transfer->hasManyProducts as $key => $item)
                                                @php
                                                    $total_receive_qty += $item->receive_qty;
                                                    $total_damage_qty  += $item->damage_qty;
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $key+1 }}</td>
                                                    <td class="text-left">{{ $item->product->name}}<br><b>Code: </b>{{ $item->product->code }}</td>
                                                    <td class="text-center">{{ $item->unit->unit_name }}</td>
                                                    <td class="text-center">{{ number_format($item->transfer_qty,2,'.',',') }}</td>
                                                    <td class="text-center">{{ number_format($item->receive_qty,2,'.',',') }}</td>
                                                    <td class="text-center">{{ number_format($item->damage_qty,2,'.',',') }}</td>
                                                    @if(empty(Auth::user()->warehouse_id))
                                                    <td class="text-right">{{ number_format($item->net_unit_cost,2,'.',',') }}</td>
                                                    <td class="text-right">{{ number_format($item->total,2,'.',',') }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td class="text-left" colspan="3"><b>TOTAL</b></td>
                                                <td class="text-center"><b>{{ number_format($transfer->total_qty,2,'.',',') }}</b></td>
                                                <td class="text-center"><b>{{ number_format($total_receive_qty,2,'.',',') }}</b></td>
                                                <td class="text-center"><b>{{ number_format($total_damage_qty,2,'.',',') }}</b></td>
                                                @if(empty(Auth::user()->warehouse_id))
                                                <td></td>
                                                <td class="text-right"><b>{{ number_format($transfer->grand_total,2,'.',',') }}</b></td>
                                                @endif
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>

                                <table style="width: 100%;">
                                    <tr>
                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:left;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $transfer->created_by }}</b>
                                                    <br> {{ date('d-M-Y h:i:s A',strtotime($transfer->created_at)) }}</p>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Created By</p>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:right;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $transfer->receiver->name }}</b>
                                                    <br> {{ $transfer->receive_date ? date('d-M-Y h:i:s A',strtotime($transfer->receive_date)) : '' }}</p>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Received By</p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$("#kt_body").addClass("aside-minimize");
$(document).ready(function () {
    
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });
});

</script>
@endpush