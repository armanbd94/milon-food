<div class="col-md-12">
    <form id="receive_form" method="post">
        @csrf
        <table class="table table-borderless">
            <tbody>
                <tr><td width="30%"><b>Challan No.</b></td><td><b>: {{ $transfer->challan_no }}</b></td></tr>
                <tr><td><b>Date</b></td><td><b>: {{ date('d-M-Y',strtotime($transfer->transfer_date)) }}</b></td></tr>
            </tbody>
        </table>
        <table class="table table-bordered">
            <thead class="bg-primary">
                <th class="text-center">SL.</th>
                <th>Product</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Transfered Qty</th>
                <th class="text-center">Receive Qty</th>
                <th class="text-center">Damage Qty</th>
            </thead>
            <tbody>
                @php
                    $total_damage_cost = 0;
                @endphp
                @if (!$transfer->hasManyProducts->isEmpty())
                    @foreach ($transfer->hasManyProducts as $key => $item)
                    @php
                        $total_damage_cost += $item->net_unit_cost * $item->damage_qty;
                    @endphp
                        <tr>
                            <td class="text-center">{{ $key+1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-center">{{ $item->unit->unit_name }}</td>
                            <td class="text-center">
                                {{ $item->transfer_qty }}
                                <input type="hidden" class="transfer_qty" name="products[{{ $key+1 }}][transfer_qty]" id="products_{{ $key+1 }}_transfer_qty" value="{{ $item->transfer_qty }}" />
                                <input type="hidden" name="products[{{ $key+1 }}][net_unit_cost]" id="products_{{ $key+1 }}_net_unit_cost" value="{{ $item->net_unit_cost }}" />
                                <input type="hidden" class="damage_cost" name="products[{{ $key+1 }}][damage_cost]" id="products_{{ $key+1 }}_damage_cost" value="{{ $item->net_unit_cost * $item->damage_qty  }}" />
                            </td>
                            <td class="text-center">
                                <input type="text" class="form-control text-center receive_qty" name="products[{{ $key+1 }}][receive_qty]" id="products_{{ $key+1 }}_receive_qty" value="{{ $item->receive_qty ? $item->receive_qty : '' }}" onkeyup="calculateQty({{ $key+1 }})" />
                            </td>
                            <td class="text-center">
                                <input type="text" class="form-control text-center damage_qty" name="products[{{ $key+1 }}][damage_qty]" id="products_{{ $key+1 }}_damage_qty" value="{{ $item->damage_qty ? $item->damage_qty : '' }}" readonly />
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <thead  class="bg-primary">
                    <th colspan="3">Total</th>
                    <th id="total-qty" class="text-center">{{ $transfer->total_qty }}</th>
                    <th id="receive-qty" class="text-center">{{ $transfer->receive_qty }}</th>
                    <th id="damage-qty" class="text-center">{{ $transfer->damage_qty }}</th>
                </thead>
            </tfoot>
        </table>
        <input type="hidden" name="transfer_id" id="transfer_id" value="{{ $transfer->id }}" />
        <input type="hidden" name="receive_qty" id="receive_qty" />
        <input type="hidden" name="damage_qty" id="damage_qty" />
        <input type="hidden" name="total_damage_cost" id="total_damage_cost" value="{{ $total_damage_cost  }}" />
    </form>
</div>