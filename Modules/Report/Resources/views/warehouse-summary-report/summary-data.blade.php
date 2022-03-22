<!-- ## Product Sales Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h2 class="card-label text-white">Material Purchase Grand Value</h2>
            </div>
        </div>
        <div class="card-body">
            @if ($material_purchase_data->material_purchase_grand_value)
                <h5>{{ number_format($material_purchase_data->material_purchase_grand_value,2,'.',',') }}Tk</h5>
                @if($material_purchase_data->material_purchase_grand_value > 0)
                <h5>{{ numberTowords($material_purchase_data->material_purchase_grand_value) }} Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Product Sales Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h2 class="card-label text-white">Product Sales Grand Value</h2>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
                <h5>{{ number_format($product_sale_data->product_sales_grand_value,2,'.',',') }}Tk</h5>
                @if($product_sale_data->product_sales_grand_value > 0)
                <h5>{{ numberTowords($product_sale_data->product_sales_grand_value) }} Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
            
        </div>
    </div>
</div>

<!-- ## Sales Collection Received Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Sales Collection Received Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
                <h5>{{ number_format($product_sale_data->sales_collection_received_value,2,'.',',') }}Tk</h5>
                @if($product_sale_data->sales_collection_received_value > 0)
                <h5>{{ numberTowords($product_sale_data->sales_collection_received_value) }} Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Product Sales Due Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Product Sales Due Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
                <h5>{{ number_format($product_sale_data->product_sales_due_value,2,'.',',') }}Tk</h5>
                @if($product_sale_data->product_sales_due_value > 0)
                <h5>{{ numberTowords($product_sale_data->product_sales_due_value) }} Taka</h5>
                @else 
                <h5>Zero Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Total Due Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Total Due Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            @php $total_due = 0; @endphp
            @if (!$total_due_grand_values->isEmpty())
            @foreach ($total_due_grand_values->chunk(10) as $chunk)
                    @foreach ($chunk as $value)
                    @php $total_due += $value->due_amount; @endphp
                    @endforeach
            @endforeach
            @endif
            <h5>{{ number_format($total_due,2,'.',',') }}Tk</h5>
            @if($total_due > 0)
            <h5>{{ numberTowords($total_due) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Damage Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Sales Return & Damage Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($total_damage_value,2,'.',',') }}Tk</h5>
            @if($total_damage_value > 0)
            <h5>{{ numberTowords($total_damage_value) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Warehouse Expense Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Warehouse Expense Value</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($warehouse_expense,2,'.',',') }}Tk</h5>
            @if($warehouse_expense > 0)
            <h5>{{ numberTowords($warehouse_expense) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Customer Discount Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Customer Discount Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if($product_sale_data)
            <h5>{{ number_format($product_sale_data->customer_discount_grand_value,2,'.',',') }}Tk</h5>
            @if($product_sale_data->customer_discount_grand_value > 0)
            <h5>{{ numberTowords($product_sale_data->customer_discount_grand_value) }} Taka</h5>
            @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Supplier Due Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Supplier Due</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($total_supplier_due_amount,2,'.',',') }}Tk</h5>
            @if($total_supplier_due_amount > 0)
            <h5>{{ numberTowords($total_supplier_due_amount) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## SR Commission Due Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Salesman Commission Due</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($total_sr_commission_due_amount,2,'.',',') }}Tk</h5>
            @if($total_sr_commission_due_amount > 0)
            <h5>{{ numberTowords($total_sr_commission_due_amount) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>