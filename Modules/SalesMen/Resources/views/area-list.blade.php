@if (!$salesmen_areas->isEmpty())
    @foreach ($salesmen_areas as $key => $item)
    <tr>
        <td>
            <select class="form-control selectpicker" name="areas[{{ $key + 1 }}][area_id]" id="areas_{{ $key + 1 }}_area_id" class="area" data-live-search="true" >
                <option value="">Select Please</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" {{ ($area->id == $item->area_id) ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
        </td>
        <td></td>
    </tr>
    @endforeach
@else
    <tr>
        <td>
            <select class="form-control selectpicker" name="areas[1][area_id]" id="areas_{{ $key }}_area_id" class="area" data-live-search="true" >
                <option value="">Select Please</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
@endif
