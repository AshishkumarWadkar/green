<input type="hidden" name="id" id="modal_enquiry_id">

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="modal_enquiry_date" class="form-label">Enquiry Date <span class="text-danger">*</span></label>
    <input type="text" class="form-control flatpickr-modal" id="modal_enquiry_date" name="enquiry_date" />
  </div>
  <div class="col-md-6 mb-3">
    <label for="modal_customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="modal_customer_name" name="customer_name" placeholder="Enter customer name" />
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="modal_mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="modal_mobile_number" name="mobile_number" placeholder="Enter mobile number" maxlength="10" inputmode="numeric" />
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_alternate_mobile" class="form-label">Alternate Mobile</label>
    <input type="text" class="form-control" id="modal_alternate_mobile" name="alternate_mobile" placeholder="Enter alternate mobile" maxlength="10" inputmode="numeric" />
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_email" class="form-label">Email</label>
    <input type="email" class="form-control" id="modal_email" name="email" placeholder="Enter email" />
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="modal_location" class="form-label">Location</label>
    <input type="text" class="form-control" id="modal_location" name="location" placeholder="Enter location" />
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_pincode" class="form-label">Pincode</label>
    <input type="text" class="form-control" id="modal_pincode" name="pincode" placeholder="Enter pincode" maxlength="6" inputmode="numeric" />
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_enquiry_type" class="form-label">Enquiry Type</label>
    <select class="form-select modal-select2" id="modal_enquiry_type" name="enquiry_type">
      <option value="">Select enquiry type</option>
      <option value="Residential">Residential</option>
      <option value="Industrial">Industrial</option>
      <option value="Commercial">Commercial</option>
    </select>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="modal_enquiry_source_id" class="form-label">Source of Enquiry <span class="text-danger">*</span></label>
    <select class="form-select modal-select2" id="modal_enquiry_source_id" name="enquiry_source_id">
      <option value="">Select source</option>
      @foreach($sources as $source)
        <option value="{{ $source->id }}">{{ $source->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-6 mb-3">
    <label for="modal_product_service" class="form-label">Product / Service Interested In</label>
    <input type="text" class="form-control" id="modal_product_service" name="product_service" placeholder="Enter product/service" />
  </div>
</div>

<div class="row">
  @if(auth()->user()->hasRole('Sales'))
  <div class="col-md-6 mb-3">
    <label for="modal_assigned_to_readonly" class="form-label">Assigned To <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="modal_assigned_to_readonly" value="{{ auth()->user()->name }}" readonly>
    <input type="hidden" name="assigned_to" id="modal_assigned_to" value="{{ auth()->id() }}">
  </div>
  @else
  <div class="col-md-6 mb-3">
    <label for="modal_assigned_to" class="form-label">Assigned To <span class="text-danger">*</span></label>
    <select class="form-select modal-select2" id="modal_assigned_to" name="assigned_to">
      <option value="">Select user</option>
      @foreach($users as $user)
        <option value="{{ $user->id }}">{{ $user->name }}</option>
      @endforeach
    </select>
  </div>
  @endif
  <div class="col-md-6 mb-3">
    <label for="modal_lead_type" class="form-label">Lead Type <span class="text-danger">*</span></label>
    <select class="form-select modal-select2" id="modal_lead_type" name="lead_type">
      <option value="">Select lead type</option>
      <option value="Hot">Hot</option>
      <option value="Cold">Cold</option>
      <option value="Warm">Warm</option>
    </select>
  </div>
</div>

<div class="mb-3">
  <label for="modal_initial_remark" class="form-label">Remark <span class="text-danger">*</span></label>
  <textarea class="form-control" id="modal_initial_remark" name="initial_remark" rows="3" placeholder="Enter remark"></textarea>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="modal_next_follow_up_date" class="form-label">Next Follow-up Date <span class="text-danger">*</span></label>
    <input type="text" class="form-control flatpickr-modal" id="modal_next_follow_up_date" name="next_follow_up_date" placeholder="Select follow-up date" />
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_capacity_kw" class="form-label">Capacity</label>
    <div class="input-group">
      <input type="number" min="0" step="1" class="form-control" id="modal_capacity_kw" name="capacity_kw" placeholder="Enter capacity" />
      <span class="input-group-text">KW</span>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_finance_type" class="form-label">Finance Type</label>
    <select class="form-select modal-select2" id="modal_finance_type" name="finance_type">
      <option value="">Select finance type</option>
      <option value="Credit">Credit</option>
      <option value="Cash">Cash</option>
      <option value="EMI">EMI</option>
      <option value="Other">Other</option>
    </select>
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="modal_shadow_free_area_sqft" class="form-label">Shadow Free Area</label>
    <div class="input-group">
      <input type="number" min="0" step="1" class="form-control" id="modal_shadow_free_area_sqft" name="shadow_free_area_sqft" placeholder="Enter area" />
      <span class="input-group-text">Sqft</span>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_customer_profession" class="form-label">Customer Profession</label>
    <select class="form-select modal-select2" id="modal_customer_profession" name="customer_profession">
      <option value="">Select profession</option>
      @foreach($professions as $profession)
        <option value="{{ $profession->name }}">{{ $profession->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4 mb-3">
    <label for="modal_consumer_number" class="form-label">Consumer Number</label>
    <input type="text" class="form-control" id="modal_consumer_number" name="consumer_number" placeholder="Enter consumer number" />
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label for="modal_status" class="form-label">Status <span class="text-danger">*</span></label>
    <select class="form-select modal-select2" id="modal_status" name="status">
      <option value="Pending">Pending</option>
      <option value="Accepted">Accepted</option>
      <option value="Cancelled">Cancelled</option>
    </select>
  </div>
</div>
