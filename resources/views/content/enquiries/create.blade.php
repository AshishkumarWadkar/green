@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Add New Enquiry')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Add New Enquiry</h5>
  </div>
  <div class="card-body">
    <form id="addEnquiryForm" novalidate>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="enquiry_date" class="form-label">Enquiry Date <span class="text-danger">*</span></label>
          <input type="text" class="form-control flatpickr" id="enquiry_date" name="enquiry_date" value="{{ date('Y-m-d') }}" />
        </div>
        <div class="col-md-6 mb-3">
          <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter customer name" />
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter mobile number" />
        </div>
        <div class="col-md-4 mb-3">
          <label for="alternate_mobile" class="form-label">Alternate Mobile</label>
          <input type="text" class="form-control" id="alternate_mobile" name="alternate_mobile" placeholder="Enter alternate mobile" />
        </div>
        <div class="col-md-4 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" />
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="enquiry_source_id" class="form-label">Source of Enquiry <span class="text-danger">*</span></label>
          <select class="form-select select2" id="enquiry_source_id" name="enquiry_source_id">
            <option value="">Select source</option>
            @foreach($sources as $source)
              <option value="{{ $source->id }}">{{ $source->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label for="product_service" class="form-label">Product / Service Interested In</label>
          <input type="text" class="form-control" id="product_service" name="product_service" placeholder="Enter product/service" />
        </div>
      </div>

      <div class="row">
        @if(auth()->user()->hasRole('Sales'))
        <div class="col-md-6 mb-3">
          <label for="assigned_to_readonly" class="form-label">Assigned To <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="assigned_to_readonly" value="{{ auth()->user()->name }}" readonly>
          <input type="hidden" name="assigned_to" id="assigned_to" value="{{ auth()->id() }}">
        </div>
        @else
        <div class="col-md-6 mb-3">
          <label for="assigned_to" class="form-label">Assigned To <span class="text-danger">*</span></label>
          <select class="form-select select2" id="assigned_to" name="assigned_to">
            <option value="">Select user</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="col-md-6 mb-3">
          <label for="lead_type" class="form-label">Lead Type <span class="text-danger">*</span></label>
          <select class="form-select select2" id="lead_type" name="lead_type">
            <option value="Hot">Hot</option>
            <option value="Cold">Cold</option>
            <option value="Warm">Warm</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label for="initial_remark" class="form-label">Initial Remark</label>
        <textarea class="form-control" id="initial_remark" name="initial_remark" rows="3" placeholder="Enter initial remark"></textarea>
      </div>

      <input type="hidden" name="status" value="Pending">

      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('enquiries.index') }}" class="btn btn-label-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" id="btn_save">
          <i class="ti ti-device-floppy me-1"></i> Save Enquiry
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-enquiries-create.js'])
@endsection
