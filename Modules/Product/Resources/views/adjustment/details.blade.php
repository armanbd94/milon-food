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
                    <a href="{{ route('adjustment') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h6>Adjustment No.: {{ $adjustment->adjustment_no }}</h6>
                            <h6>Warehouse: {{ $adjustment->warehouse->name }}</h6>
                            <h6>Date: {{ date('d-M-Y',strtotime($adjustment->created_at)) }}</h6>
                        </div>

                        <div class="col-md-12 pt-5">
                            <table class="table table-bordered" id="product_table">
                                <thead class="bg-primary">
                                    <th width="35%">Name</th>
                                    <th width="10%" class="text-center">Base Unit</th>
                                    <th width="10%" class="text-center">Qty Base Unit</th>
                                    <th width="10%" class="text-right">Base Unit Price</th>
                                    <th width="10%" class="text-right">Tax</th>
                                    <th width="15%" class="text-right">Sub Total</th>
                                </thead>
                                <tbody>
                                    @if (!$adjustment->products->isEmpty())
                                        @foreach ($adjustment->products as $key => $adjustment_product)
                                            @php
                                                $base_unit = DB::table('units')->find($adjustment_product->pivot->base_unit_id);
                                                $unit_name = $base_unit ? $base_unit->unit_name.' ('.$base_unit->unit_code.')' : '';
                                            @endphp
                                            <tr>
                                                <td>{{  $adjustment_product->name.' - ('.$adjustment_product->code.')' }}</td>
                                                <td class="text-center">{{ $unit_name }}</td>
                                                <td class="text-center">{{ $adjustment_product->pivot->base_unit_qty }}</td>
                                                <td class="text-right">{{ $adjustment_product->pivot->base_unit_price }}</td>
                                                <td class="text-right">{{ number_format($adjustment_product->pivot->tax,2,'.','') }}</td>
                                                <td class="text-right">{{ number_format($adjustment_product->pivot->total,2,'.','') }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot class="bg-primary">
                                    <th colspan="2" class="font-weight-bolder">Total</th>
                                    <th id="total-qty" class="text-center font-weight-bolder">{{ number_format($adjustment->total_qty,2,'.','') }}</th>
                                    <th></th>
                                    <th id="total-tax" class="text-right font-weight-bolder">{{ number_format($adjustment->total_tax,2,'.','') }}</th>
                                    <th id="total" class="text-right font-weight-bolder">{{ number_format($adjustment->grand_total,2,'.','') }}</th>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-group col-md-12">
                            <label for="shipping_cost">Note</label>
                            <p>{{ $adjustment->note }}</p>
                        </div>
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead class="bg-primary">
                                    <th><strong>Items</strong><span class="float-right" id="item">{{ $adjustment->item.'('.$adjustment->total_qty.')' }}</span></th>
                                    <th><strong>Grand Total</strong><span class="float-right" id="grand_total">{{ number_format($adjustment->grand_total,2,'.','') }}</span></th>
                                </thead>
                            </table>
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
