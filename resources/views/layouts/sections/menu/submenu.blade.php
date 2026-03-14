@php
use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    {{-- active menu method --}}
    @php
      $activeClass = null;
      $active = $configData["layout"] === 'vertical' ? 'active open':'active';
      $currentRouteName = Route::currentRouteName();
      $currentFullUrl = request()->fullUrl();
      $currentBaseUrl = request()->url();

      // 1. Check for exact URL match first (including query parameters)
      if (isset($submenu->url) && url($submenu->url) === $currentFullUrl) {
        $activeClass = 'active';
      }
      // 2. Base URL match (handles pagination etc)
      elseif (isset($submenu->url) && url($submenu->url) === $currentBaseUrl) {
        // Special Case: Don't activate the general one if a more specific query match exists in the menu
        $isGeneralMatch = true;
        if (isset($menu)) {
            foreach($menu as $item) {
                if (isset($item->url) && str_contains($item->url, '?') && url($item->url) === $currentFullUrl) {
                    $isGeneralMatch = false;
                    break;
                }
            }
        }
        if ($isGeneralMatch) {
            $activeClass = 'active';
        }
      }
      // 3. Route Name match
      elseif ($currentRouteName === ($submenu->slug ?? '')) {
        $activeClass = 'active';
      }
      // 3. Check for nested routes
      elseif (isset($submenu->slug) && $submenu->slug !== null) {
        if (gettype($submenu->slug) === 'array') {
          foreach($submenu->slug as $slug){
            if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
              $activeClass = 'active';
              break;
            }
          }
        }
        else {
          if (str_contains($currentRouteName, $submenu->slug) && strpos($currentRouteName, $submenu->slug) === 0) {
            $activeClass = 'active';
          }
          elseif (str_contains($submenu->slug, '.') && str_contains($currentRouteName, $submenu->slug)) {
            $activeClass = 'active';
          }
        }
      }
      // If submenu has nested submenu, check those too
      elseif (isset($submenu->submenu)) {
        if (gettype($submenu->slug) === 'array') {
          foreach($submenu->slug as $slug){
            if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
              $activeClass = $active;
              break;
            }
          }
        }
        else{
          if (isset($submenu->slug) && str_contains($currentRouteName, $submenu->slug) && strpos($currentRouteName, $submenu->slug) === 0) {
            $activeClass = $active;
          }
        }
      }
    @endphp

      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
          <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
