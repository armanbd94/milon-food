<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">

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
        <form id="store_or_update_form" method="post" enctype="multipart/form-data">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="row">
                            <input type="hidden" name="update_id" id="update_id"/>
                                <x-form.textbox labelName="Name" name="name" required="required" col="col-md-6" placeholder="Enter name"/>
                                {{-- <x-form.textbox labelName="Username" name="username" required="required" col="col-md-6" placeholder="Enter username"/> --}}
                                <x-form.textbox labelName="Phone No." name="phone" required="required" col="col-md-6" placeholder="Enter phone number"/>
                                <x-form.textbox labelName="Email" name="email" type="email" col="col-md-6" placeholder="Enter email"/>

                                {{-- <div class="col-md-6 form-group">
                                    <label for="site_title">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control bg-brand" name="password" id="password">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning" id="generate_password" style="border:0;cursor: pointer;" data-toggle="tooltip" data-theme="dark" title="Generate Password">
                                                <i class="fas fa-lock text-white"></i>
                                            </span>
                                        </div>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;">
                                                <i class="fas fa-eye toggle-password text-white" toggle="#password" style="cursor: pointer;"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="col-md-6 form-group">
                                    <label for="site_title">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control bg-brand" name="password_confirmation" id="password_confirmation">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;">
                                                <i class="fas fa-eye toggle-password text-white" toggle="#password_confirmation" style="cursor: pointer;"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div> --}}

                                <x-form.selectbox labelName="District" name="district_id" col="col-md-6" class="selectpicker">
                                    @if (!$districts->isEmpty())
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.textbox labelName="NID No." name="nid_no" col="col-md-6" placeholder="Enter NID no."/>
                                <x-form.textbox labelName="Monthly Target Value" name="monthly_target_value" col="col-md-6" placeholder="Enter monthly target value"/>
                                <x-form.textarea labelName="Address" name="address" col="col-md-6" placeholder="Enter address"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-md-12 mb-0">
                            <label for="logo" class="form-control-label">Person Image</label>
                            <div class="col=md-12 px-0  text-center">
                                <div id="avatar">
                
                                </div>
                            </div>
                            <input type="hidden" name="old_avatar" id="old_avatar">
                        </div>
                    </div>
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