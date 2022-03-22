<div class="col-md-12">
    <table class="table table-borderless">
        <tbody>
            @if (empty(Auth::user()->warehouse_id))
            <tr><td><b>Warehouse</b></td><td><b>:</b>{{ $closing->warehouse->name }}</td></tr>
            @endif
            <tr><td><b>Received</b></td><td><b>:</b>{{ number_format($closing->cash_in,2,'.',',') }}</td></tr>
            <tr><td><b>Payment</b></td><td><b>:</b>{{ number_format($closing->cash_out,2,'.',',') }}</td></tr>
            <tr><td><b>Closing Balance</b></td><td><b>:</b>{{ number_format($closing->closing_amount,2,'.',',') }}</td></tr>
        </tbody>
    </table>
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-primary">
                <th><b>Note Name</b></th>
                <th class="text-center"><b>Pcs</b></th>
                <th class="text-right"><b>Amount</b></th>
            </thead>
            <tbody>
                <tr><td><b>1000</b></td><td class="text-center"><b>{{ $closing->thousands ?? '' }}</b></td><td  class="text-right">{{ $closing->thousands ? number_format(1000 * $closing->thousands,2,'.',',') : '' }}</td></tr>
                <tr><td><b>500</b></td><td class="text-center"><b>{{ $closing->five_hundred ?? '' }}</b></td><td  class="text-right">{{ $closing->five_hundred ? number_format(1000 * $closing->five_hundred,2,'.',',') : '' }}</td></tr>
                <tr><td><b>200</b></td><td class="text-center"><b>{{ $closing->two_hundred ?? '' }}</b></td><td  class="text-right">{{ $closing->two_hundred ? number_format(1000 * $closing->two_hundred,2,'.',',') : '' }}</td></tr>
                <tr><td><b>100</b></td><td class="text-center"><b>{{ $closing->hundred ?? '' }}</b></td><td  class="text-right">{{ $closing->hundred ? number_format(1000 * $closing->hundred,2,'.',',') : '' }}</td></tr>
                <tr><td><b>50</b></td><td class="text-center"><b>{{ $closing->fifty ?? '' }}</b></td><td  class="text-right">{{ $closing->fifty ? number_format(1000 * $closing->fifty,2,'.',',') : '' }}</td></tr>
                <tr><td><b>20</b></td><td class="text-center"><b>{{ $closing->twenty ?? '' }}</b></td><td  class="text-right">{{ $closing->twenty ? number_format(1000 * $closing->twenty,2,'.',',') : '' }}</td></tr>
                <tr><td><b>10</b></td><td class="text-center"><b>{{ $closing->ten ?? '' }}</b></td><td  class="text-right">{{ $closing->ten ? number_format(1000 * $closing->ten,2,'.',',') : '' }}</td></tr>
                <tr><td><b>5</b></td><td class="text-center"><b>{{ $closing->five ?? '' }}</b></td><td  class="text-right">{{ $closing->five ? number_format(1000 * $closing->five,2,'.',',') : '' }}</td></tr>
                <tr><td><b>2</b></td><td class="text-center"><b>{{ $closing->two ?? '' }}</b></td><td  class="text-right">{{ $closing->two ? number_format(1000 * $closing->two,2,'.',',') : '' }}</td></tr>
                <tr><td><b>1</b></td><td class="text-center"><b>{{ $closing->one  ?? '' }}</b></td><td  class="text-right">{{  $closing->one ? number_format(1000 * $closing->one,2,'.',',') : '' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>