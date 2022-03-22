@php
    $grand_total = 0;
@endphp
@if (!$warehouses->isEmpty())
    @foreach ($warehouses as $index => $warehouse)
        @if (!$warehouse->products->isEmpty())
            <div class="col-md-12 text-center"><h3 class="py-3 bg-warning text-white" style="max-width:500px;margin: 50px auto 10px auto;">{{ $product->name }}</h3></div>
            @php 
            $total_stock_value = 0; 
            @endphp
                
            <table id="dataTable" class="table table-bordered table-hover mb-5">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center">Stock Unit</th>
                        <th class="text-right">Price</th>
                        <th class="text-center">Stock Qty</th>
                        <th class="text-right">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $total_base_unit_qty = 0; 
                        $total = 0; 
                    @endphp
                    @if($product_id)
                        @foreach ($warehouse->products as $key => $item)
                            @if($product_id == $item->id && $item->pivot->qty > 0)
                                @php
                                    $total_base_unit_qty += $item->pivot->qty;
                                    $total += ($item->pivot->qty * $item->base_unit_price);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $item->base_unit->unit_name.' ('.$item->base_unit->unit_code.')' }}</td>
                                    <td class="text-right">{{ number_format($item->base_unit_price,2,'.','') }}</td>
                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                    <td class="text-right">{{ number_format(($item->pivot->qty * $item->base_unit_price),2,'.','') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        @foreach ($warehouse->products as $key => $item)
                            @if($item->pivot->qty > 0)
                                @php
                                    $total_base_unit_qty += $item->pivot->qty;
                                    $total += ($item->pivot->qty * $item->base_unit_price);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $item->base_unit->unit_name.' ('.$item->base_unit->unit_code.')' }}</td>
                                    <td class="text-right">{{ number_format($item->base_unit_price,2,'.','') }}</td>
                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                    <td class="text-right">{{ number_format(($item->pivot->qty * $item->base_unit_price),2,'.','') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-primary">
                        <th colspan="2" style="text-align: right !important;font-weight:bold;color:white;">Total</th>
                        <th style="text-align: center !important;font-weight:bold;color:white;">{{ number_format($total_base_unit_qty,2,'.','') }}</th>
                        <th style="text-align: right !important;font-weight:bold;color:white;">{{ number_format($total,2,'.','') }}</th>
                        @php
                        $total_stock_value += $total;
                    @endphp
                    </tr>
                </tfoot>
            </table>
            @php
                $grand_total += $total_stock_value;
            @endphp
        @endif
    @endforeach
    <h3 class="bg-dark text-white font-weight-bolder p-3 text-right">Total Stock Value = {{ number_format($grand_total,2,'.','') }}</h3>
@else 
    <div class="col-md-12 text-center"><h3 class="py-3 bg-danger text-white">Stock Data is Empty</h3></div>
@endif