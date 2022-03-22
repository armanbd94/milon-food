<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Name</b></td><td><b>:</b></td><td>{{ $asm->name }}</td>
                <td></td>
            </tr>
            <tr>
                <td><b>Phone</b></td><td><b>:</b></td><td>{{ $asm->phone }}</td>
                <td><b>Email</b></td><td><b>:</b></td><td>{!! $asm->email ? $asm->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>' !!}</td>
            </tr>
            <tr>
                <td><b>Warehouse</b></td><td><b>:</b></td><td>{{ $asm->warehouse->name }}</td>
                <td><b>District</b></td><td><b>:</b></td><td>{{ $asm->district->name }}</td>
            </tr>
            <tr>
                <td><b>NID No.</b></td><td><b>:</b></td><td>{{ $asm->nid_no }}</td>
                <td><b>Monthly Target Value</b></td><td><b>:</b></td><td>{{ number_format($asm->monthly_target_value,2,'.','') }} Tk</td>
            </tr>
            <tr>
                <td><b>Status</b></td><td><b>:</b></td><td>{!! STATUS_LABEL[$asm->status] !!}</td>
                <td><b>Address</b></td><td><b>:</b></td><td>{{  $asm->address  }}</td>
            </tr>
            <tr>
                <td><b>Created By</b></td><td><b>:</b></td><td>{{  $asm->created_by  }}</td>
                <td><b>Create Date</b></td><td><b>:</b></td><td>{{  $asm->created_at ? date(config('settings.date_format'),strtotime($asm->created_at)) : ''  }}</td>
            </tr>
            <tr>
                <td><b>Modified By</b></td><td><b>:</b></td><td>{{  $asm->modified_by  }}</td>
                <td><b>Modification Date</b></td><td><b>:</b></td><td>@if($asm->modified_by) {{  $asm->updated_at ? date(config('settings.date_format'),strtotime($asm->updated_at)) : ''  }}@endif</td>
            </tr>
        </table>
    </div>
</div>
<div class="col-md-3 text-center">
    @if($asm->avatar)
        <img src='storage/{{ ASM_AVATAR_PATH.$asm->avatar }}' alt='{{ $asm->name }}' style='width:150px;'/>
    @else
        <img src='images/male.svg' alt='Default Image' style='width:200px;'/>
    @endif
</div>