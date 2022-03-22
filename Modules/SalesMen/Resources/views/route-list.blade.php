@if (!$salesmen_routes->isEmpty())
    @foreach ($salesmen_routes as $key => $item)
    <tr>
        <td>
            <input type="hidden" name="routes[{{ $item->day }}][day]" value="{{ $item->day }}">
            {{ DAYS[$item->day] }}
        </td>
        <td>
            <select class="form-control selectpicker" name="routes[{{ $key + 1 }}][route_id]" id="routes_{{ $key + 1 }}_route_id" class="route" data-live-search="true" >
                <option value="">Select Please</option>
                @foreach ($routes as $route)
                    <option value="{{ $route->id }}" {{ ($route->id == $item->route_id) ? 'selected' : '' }}>{{ $route->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
    @endforeach
@else
    @foreach (DAYS as $key => $value)
    <tr>
        <td>
            <input type="hidden" name="routes[{{ $key }}][day]" value="{{ $key }}">
            {{ $value }}
        </td>
        <td>
            <select class="form-control selectpicker" name="routes[{{ $key }}][route_id]" id="routes_{{ $key }}_route_id" class="route" data-live-search="true" >
                <option value="">Select Please</option>
                @foreach ($routes as $route)
                    <option value="{{ $route->id }}">{{ $route->name }}</option>
                @endforeach
            </select>
        </td>
    </tr>
    @endforeach
@endif
