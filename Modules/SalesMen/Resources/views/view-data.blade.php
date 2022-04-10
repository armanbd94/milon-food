<div class="col-md-12">
    <div class="row">
        <div class="table-responsive col-9">
            <table class="table table-borderless">
                <tr>
                    <td><b>Name</b></td><td><b>:</b></td><td>{{ $salesmen->name }}</td>
                    <td><b>Username</b></td><td><b>:</b></td><td>{{ $salesmen->username }}</td>
                </tr>
                <tr>
                    <td><b>Phone</b></td><td><b>:</b></td><td>{{ $salesmen->phone }}</td>
                    <td><b>Email</b></td><td><b>:</b></td><td>{!! $salesmen->email ? $salesmen->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>' !!}</td>
                </tr>
                
                <tr>
                    <td><b>ASm Name</b></td><td><b>:</b></td><td>{{  $salesmen->asm->name  }}%</td>
                    <td><b>Warehouse</b></td><td><b>:</b></td><td>{{  $salesmen->warehouse->name  }}</td>
                    
                </tr>
                <tr>
                    <td><b>District</b></td><td><b>:</b></td><td>{{  $salesmen->district->name  }}</td>
                    <td><b>Upazila</b></td><td><b>:</b></td><td>{{  $salesmen->upazila->name  }}</td>
                    
                </tr>
                <tr>
                    <td><b>Created By</b></td><td><b>:</b></td><td>{{  $salesmen->created_by  }}</td>
                    <td><b>Modified By</b></td><td><b>:</b></td><td>{{  $salesmen->modified_by  }}</td>
                    
                </tr>
                <tr>
                    <td><b>Create Date</b></td><td><b>:</b></td><td>{{  $salesmen->created_at ? date(config('settings.date_format'),strtotime($salesmen->created_at)) : ''  }}</td>
                    <td><b>Modified Date</b></td><td><b>:</b></td><td>{{  $salesmen->modified_by ? ($salesmen->updated_at ? date(config('settings.date_format'),strtotime($salesmen->updated_at)) : '') : ''  }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-3 text-center" >
            @if($salesmen->avatar)
                <img src='storage/{{ SALESMEN_AVATAR_PATH.$salesmen->avatar }}' alt='{{ $salesmen->name }}' style="width:150px;box-shadow: 0 2px 10px 0 rgba(0,0,0,0.5);
                border: 5px solid #fff;"/>
            @else
                <img src='images/male.svg' alt='Default Image' style="width:150px;box-shadow: 0 2px 10px 0 rgba(0,0,0,0.5);
                border: 5px solid #fff;"/>
            @endif
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <h6 class="bg-primary text-center text-white" style="width: 250px;padding: 5px; margin: 10px auto 5px auto;">Visiting Areas</h6>
        <table class="table table-bordered">
            <thead class="bg-primary">
                <th class="text-center">SL.</th>
                <th>Area</th>
            </thead>
            <tbody>
                @if (!$salesmen->areas->isEmpty())
                    @foreach ($salesmen->areas as $key => $area)
                        <tr>
                            <td class="text-center">{{ $key+1 }}</td>
                            <td>{{ $area->name }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>