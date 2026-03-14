@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <h4 class="fw-bold mb-4">Dashboard</h4>
  </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
  @can('view-enquiries')
  <div class="col-xl-3 col-md-6 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-label-primary rounded p-2 mb-2"><i class="ti ti-file-text ti-sm"></i></span>
            <h5 class="card-title mb-1">Total Enquiries</h5>
            <h3 class="mb-0">{{ $totalEnquiries }}</h3>
          </div>
          <p class="text-primary mb-0"><i class="ti ti-chart-bar"></i></p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-label-success rounded p-2 mb-2"><i class="ti ti-check ti-sm"></i></span>
            <h5 class="card-title mb-1">Accepted Leads</h5>
            <h3 class="mb-0">{{ $acceptedEnquiries }}</h3>
          </div>
          <span class="badge bg-label-success">{{ $conversionRate }}%</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-label-danger rounded p-2 mb-2"><i class="ti ti-flame ti-sm"></i></span>
            <h5 class="card-title mb-1">Hot Leads</h5>
            <h3 class="mb-0">{{ $hotLeads }}</h3>
          </div>
          <p class="text-danger mb-0"><i class="ti ti-trending-up"></i></p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-label-warning rounded p-2 mb-2"><i class="ti ti-clock ti-sm"></i></span>
            <h5 class="card-title mb-1">Pending</h5>
            <h3 class="mb-0">{{ $pendingEnquiries }}</h3>
          </div>
          <p class="text-warning mb-0"><i class="ti ti-alert-circle"></i></p>
        </div>
      </div>
    </div>
  </div>
  @endcan
</div>

<div class="row g-4 mb-4">
  @can('view-enquiries')
  <div class="col-xl-6 col-sm-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title mb-1">Enquiries Today</h5>
            <h3 class="mb-0">{{ $enquiryCountToday }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6 col-sm-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title mb-1">Enquiries This Month</h5>
            <h3 class="mb-0">{{ $enquiryCountMonth }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endcan
</div>

<div class="row g-4 mb-4">
  <div class="col-md-12">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Quick Actions</h5>
      </div>
      <div class="card-body pt-4">
        <div class="d-flex gap-3">
          @can('create-enquiries')
          <a href="{{ route('enquiries.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add New Enquiry
          </a>
          @endcan
          @can('view-enquiries')
          <a href="{{ route('enquiries.index') }}" class="btn btn-label-primary">
            <i class="ti ti-list me-1"></i> View All Enquiries
          </a>
          @endcan
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts -->
@can('view-enquiries')
<div class="row g-4 mb-4">
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Enquiry Trend (Last 14 Days)</h5>
      </div>
      <div class="card-body">
        <div id="enquiryTrendChart"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Monthly Status Comparison</h5>
      </div>
      <div class="card-body pt-4">
        <div id="monthlyStatusChart"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Status-wise Analysis</h5>
      </div>
      <div class="card-body">
        <div id="statusWiseChart"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Lead Quality (Type)</h5>
      </div>
      <div class="card-body">
        <div id="leadTypeWiseChart"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-12">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Source-wise Performance</h5>
      </div>
      <div class="card-body">
        <div id="sourceWiseChart"></div>
      </div>
    </div>
  </div>
</div>

<script>
  window.dashboardData = {
    trendDates: @json($trendDates),
    trendCounts: @json($trendCounts),
    sourceWise: @json($sourceWise),
    statusWise: @json($statusWise),
    leadTypeWise: @json($leadTypeWise),
    monthlyLabels: @json($monthlyTrendLabels),
    monthlyAccepted: @json($monthlyAccepted),
    monthlyCancelled: @json($monthlyCancelled),
    monthlyPending: @json($monthlyPending)
  };
</script>
@endcan
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-pages-home.js'])
@endsection
