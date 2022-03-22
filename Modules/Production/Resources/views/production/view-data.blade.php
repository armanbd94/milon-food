@if($below_qty > 0) <h6 class="text-danger font-weight-bolder text-center w-100">Red Marked Materials Stock Quantity is Less Than Required Quantity</h6> @endif
<table class="table table-bordered pb-5" id="material_table">
    <thead class="bg-primary">
        <th>Material</th>
        <th class="text-center">Type</th>
        <th class="text-center">Unit Name</th>
        <th class="text-center">Current Stock Qty</th>
        <th class="text-center">Total Required Qty</th>
    </thead>
    <tbody>
        @if (!empty($materials) && count($materials) > 0)
            @foreach ($materials as $item)
                <tr class="{{ $item['background'] ? $item['background'].' text-white' : '' }}">
                    <td>{{ $item['material_name'] }}</td>
                    <td class="text-center">{{ $item['type'] }}</td>
                    <td class="text-center">{{ $item['unit_name'] }}</td>
                    <td class="text-center">{{ $item['stock_qty'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="col-md-12">
    <button type="button" class="btn btn-danger btn-sm float-right" data-dismiss="modal">Close</button>
</div>