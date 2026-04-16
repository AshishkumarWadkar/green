@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Enquiry Details')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('content')
<div class="row">
  <!-- Enquiry Details -->
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Enquiry Details</h5>
        <a href="{{ route('enquiries.index') }}" class="btn btn-label-secondary btn-sm">
          <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <strong>Enquiry Date:</strong> {{ $enquiry->enquiry_date->format('d M Y') }}
          </div>
          <div class="col-md-4">
            <strong>Lead Type:</strong> 
            @php
              $ltClass = 'bg-label-primary';
              if ($enquiry->lead_type == 'Hot') $ltClass = 'bg-label-danger';
              if ($enquiry->lead_type == 'Warm') $ltClass = 'bg-label-warning';
              if ($enquiry->lead_type == 'Cold') $ltClass = 'bg-label-info';
            @endphp
            <span class="badge {{ $ltClass }}">
              {{ $enquiry->lead_type }}
            </span>
          </div>
          <div class="col-md-4">
            <strong>Status:</strong> 
            @php
              $stClass = 'bg-label-secondary';
              if ($enquiry->status == 'Accepted') $stClass = 'bg-label-success';
              if ($enquiry->status == 'Cancelled') $stClass = 'bg-label-danger';
              if ($enquiry->status == 'Pending') $stClass = 'bg-label-warning';
            @endphp
            <span class="badge {{ $stClass }}">
              {{ $enquiry->status }}
            </span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Customer Name:</strong> {{ $enquiry->customer_name }}
          </div>
          <div class="col-md-6">
            <strong>Mobile:</strong> {{ $enquiry->mobile_number }}
            @if($enquiry->alternate_mobile)
              <br><small class="text-muted">Alt: {{ $enquiry->alternate_mobile }}</small>
            @endif
          </div>
        </div>

        @if($enquiry->email)
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Email:</strong> {{ $enquiry->email }}
          </div>
        </div>
        @endif

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Source:</strong> {{ $enquiry->enquirySource->name }}
          </div>
          <div class="col-md-6">
            <strong>Product / Service:</strong> {{ $enquiry->product_service ?? '-' }}
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Assigned To:</strong> {{ $enquiry->assignedUser->name }}
          </div>
          <div class="col-md-6">
            <strong>Created By:</strong> {{ $enquiry->createdBy->name }}
          </div>
        </div>

        @if($enquiry->initial_remark)
        <div class="row mb-3">
          <div class="col-md-12">
            <strong>Initial Remark:</strong>
            <p class="mb-0">{{ $enquiry->initial_remark }}</p>
          </div>
        </div>
        @endif

        @if($enquiry->follow_up_remark)
        <div class="row mb-3">
          <div class="col-md-12">
            <strong>Follow-up Remark:</strong>
            <p class="mb-0">{{ $enquiry->follow_up_remark }}</p>
          </div>
        </div>
        @endif
      </div>
    </div>
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
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
<script>
  window.enquiryId = {{ $enquiry->id }};
</script>
@vite(['resources/assets/js/app-enquiries-show.js'])
@endsection
