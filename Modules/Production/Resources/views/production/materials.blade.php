
<div class="col-md-12 py-5 table-responsive">
    <div class="col-md-12 text-center">
        <h5 class="bg-warning text-white p-3" style="width:250px;margin: 20px auto 10px auto;">Materials</h5>
    </div>
    <table class="table table-bordered pb-5" id="material_table_{{ $tab }}">
        <thead class="bg-primary">
            <th width="30%">Material</th>
            <th width="5%" class="text-center">Type</th>
            <th width="10%" class="text-center">Unit Name</th>
            <th width="10%" class="text-right">Rate</th>
            <th width="15%" class="text-center">Stk. Avl. Qty</th>
            <th width="13%" class="text-center">Required Qty</th>
            <th width="17%" class="text-right">Total</th>
        </thead>
        <tbody>
            @if (!$materials->isEmpty())
                @foreach ($materials as $key => $item)
                <tr>
                    <td>
                        {{ $item->material_name .' ('.$item->material_code.')' }}
                        <input type="hidden" class="form-control text-center" value="{{ $item->material_id }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][material_id]" id="production_{{ $tab }}_materials_{{ $key+1 }}_material_id" data-id="{{ $key+1 }}" readonly>
                    </td>
                    <td class="text-center">
                        {{ MATERIAL_TYPE[$item->type] }}
                    </td>
                    <td class="text-center">
                        {{ $item->unit_name.' ('.$item->unit_code.')' }}
                        <input type="hidden" class="form-control" value="{{ $item->unit_id }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][unit_id]" id="production_{{ $tab }}_materials_{{ $key+1 }}_unit_id" data-id="{{ $key+1 }}">
                        
                    </td>
                    <td class="text-right">
                        {{ number_format($item->cost,2,'.','') }}
                        <input type="hidden" class="form-control text-right" value="{{ $item->cost }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][cost]" id="production_{{ $tab }}_materials_{{ $key+1 }}_cost" data-id="{{ $key+1 }}" readonly>
                    </td>
                    <td class="text-center">
                        {{ $item->qty }}
                        <input type="hidden" class="form-control text-right stock_qty" value="{{ $item->qty }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][stock_qty]" id="production_{{ $tab }}_materials_{{ $key+1 }}_stock_qty" data-id="{{ $key+1 }}">
                    </td>
                    <td>
                        <input type="text" class="form-control text-center qty" name="production[{{ $tab }}][materials][{{ $key+1 }}][qty]" id="production_{{ $tab }}_materials_{{ $key+1 }}_qty"  onkeyup="calculateRowTotal('{{ $tab }}','{{ $key+1 }}')" data-id="{{ $key+1 }}">
                    </td>
                    <td>
                        <input type="text" class="form-control text-right total" name="production[{{ $tab }}][materials][{{ $key+1 }}][total]" id="production_{{ $tab }}_materials_{{ $key+1 }}_total" data-id="{{ $key+1 }}" readonly>
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div> 