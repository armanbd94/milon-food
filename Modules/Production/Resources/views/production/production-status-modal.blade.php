<div class="modal fade" id="production_status_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
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
        <!-- Modal Body -->
        <form id="production_status_form" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="production_id" id="production_id">
                <div class="row">
                    <div class="form-group col-md-12 required">
                        <label for="">Production Status</label>
                        <select class="form-control" name="production_status" id="production_status" required>
                            @foreach (PRODUCTION_STATUS as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="production-status-btn"></button>
            </div>
        </form>
        <!-- /modal body -->
      </div>
      <!-- /modal content -->

    </div>
  </div>