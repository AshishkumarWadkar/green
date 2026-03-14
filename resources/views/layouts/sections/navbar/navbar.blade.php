@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp
<style>
.dropdown-notifications-item.dropdown-notifications-item-hover { background-color: rgba(0,0,0,.06) !important; }
.dropdown-notifications-item .dropdown-notifications-item-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
.dropdown-notifications-item:last-child { border-bottom: none !important; }
.dropdown-notifications-list:empty + .dropdown-notifications-footer { border-top: 0; }
</style>
<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
          <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo menu-text fw-bold" title="{{config('variables.templateName')}}"><span>{{config('variables.templateName')}}</span></span>
          </a>
        </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-md"></i>
          </a>
        </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        @if($configData['hasCustomizer'] == true)
          <!-- Style Switcher -->
          <div class="navbar-nav align-items-center">
            <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
              <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class='ti ti-md'></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                    <span class="align-middle"><i class='ti ti-sun ti-md me-3'></i>Light</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                    <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                    <span class="align-middle"><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <!--/ Style Switcher -->
        @endif

        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- Notifications -->
          @auth
          <li class="nav-item dropdown-notifications dropdown me-2 me-xl-3">
            <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" id="navbarNotificationsDropdown" aria-expanded="false">
              <i class="ti ti-bell ti-md"></i>
              <span class="badge rounded-pill bg-danger position-absolute top-0 end-0" id="navbarNotificationBadge" data-unread="{{ $notificationUnreadCount ?? 0 }}" @if(($notificationUnreadCount ?? 0) < 1) style="display: none;" @endif>{{ ($notificationUnreadCount ?? 0) > 9 ? '9+' : ($notificationUnreadCount ?? 0) }}</span>
              <span class="visually-hidden"><span id="navbarNotificationBadgeA11y">{{ $notificationUnreadCount ?? 0 }}</span> unread</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0 overflow-hidden" style="width: 380px;" aria-labelledby="navbarNotificationsDropdown">
              <div class="dropdown-notifications-header d-flex align-items-center justify-content-between px-4 py-3 border-bottom bg-body">
                <h6 class="mb-0 fw-semibold">Notifications</h6>
                <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary" id="notificationsMarkAllRead" @if(($notificationUnreadCount ?? 0) < 1) style="display: none;" @endif>Mark all read</a>
              </div>
              <div class="dropdown-notifications-list" style="max-height: 360px; overflow-y: auto;" id="navbarNotificationsList">
                <div class="px-4 py-4 text-center text-muted" id="notificationsLoading">
                  <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...
                </div>
              </div>
              <div class="dropdown-notifications-footer border-top bg-body px-3 py-2">
                <a class="dropdown-item rounded d-flex justify-content-center text-primary py-2" href="{{ route('enquiries.index') }}">View all enquiries</a>
              </div>
            </div>
          </li>
          @endauth

          <!-- User -->
          @php
            $userName = Auth::check() ? Auth::user()->name : 'Guest';
            $nameParts = preg_split('/\s+/', trim($userName), 2);
            $userInitials = strtoupper(
              (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
              (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
            );
            if ($userInitials === '') { $userInitials = '?'; }
          @endphp
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <span class="avatar-initial rounded-circle bg-label-primary">{{ $userInitials }}</span>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item mt-0" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-2">
                      <div class="avatar avatar-online">
                        <span class="avatar-initial rounded-circle bg-label-primary">{{ $userInitials }}</span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">
                        @if (Auth::check())
                          {{ Auth::user()->name }}
                        @else
                          Guest
                        @endif
                      </h6>
                      <small class="text-muted">
                        @if (Auth::check() && Auth::user()->roles->count() > 0)
                          {{ Auth::user()->roles->first()->name }}
                        @else
                          User
                        @endif
                      </small>
                      @if (Auth::check() && Auth::user()->reporter)
                        <small class="text-muted d-block">Reports to: {{ Auth::user()->reporter->name }}</small>
                      @endif
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider my-1 mx-n2"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">My Profile</span>
                </a>
              </li>
              @if (Auth::check() && Auth::user()->reporter)
              <li>
                <span class="dropdown-item text-muted py-2">
                  <i class="ti ti-user-up me-3 ti-md"></i><span class="align-middle">Reports to: {{ Auth::user()->reporter->name }}</span>
                </span>
              </li>
              @endif
              <li>
                <div class="dropdown-divider my-1 mx-n2"></div>
              </li>
              @if (Auth::check())
                <li>
                  <div class="d-grid px-2 pt-2 pb-1">
                    <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      <small class="align-middle">Logout</small>
                      <i class="ti ti-logout ms-2 ti-14px"></i>
                    </a>
                  </div>
                </li>
                <form method="POST" id="logout-form" action="{{ route('logout') }}">
                  @csrf
                </form>
              @else
                <li>
                  <div class="d-grid px-2 pt-2 pb-1">
                    <a class="btn btn-sm btn-danger d-flex" href="{{ route('login') }}">
                      <small class="align-middle">Login</small>
                      <i class="ti ti-login ms-2 ti-14px"></i>
                    </a>
                  </div>
                </li>
              @endif
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->
