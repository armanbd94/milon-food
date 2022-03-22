<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

      <!-- Modal Content -->
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header bg-primary">
          <h3 class="modal-title text-white" id="model-1"></h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close text-white"></i>
          </button>
        </div>
        <!-- /modal header -->
        <form id="store_or_update_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                  <input type="hidden" name="update_id" id="update_id"/>
                  <input type="hidden" name="type" value="4"/>
                  
                  <x-form.selectbox labelName="District" name="grand_grand_parent_id" required="required" col="col-md-12" class="selectpicker"  onchange="getUpazilaList(this.value,2)">
                    @if (!$locations->isEmpty())
                        @foreach ($locations as $district)
                            @if ($district->type == 1)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endif
                        @endforeach
                    @endif
                  </x-form.selectbox>
                  <x-form.selectbox labelName="Upazila" name="grand_parent_id" required="required" col="col-md-12" onchange="getRouteList(this.value,2)" class="selectpicker"/>
                  <x-form.selectbox labelName="Route" name="parent_id" required="required" col="col-md-12" class="selectpicker"/>
                  <x-form.textbox labelName="Area Name" name="name" required="required" col="col-md-12" placeholder="Enter area name"/>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>