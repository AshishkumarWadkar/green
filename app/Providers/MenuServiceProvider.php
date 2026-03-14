<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Use View Composer to filter menus after authentication
    View::composer('*', function ($view) {
      // Only process if menuData is not already set
      if (!$view->offsetExists('menuData')) {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);
        $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
        $horizontalMenuData = json_decode($horizontalMenuJson);

        // Filter menus based on permissions
        $verticalMenuData->menu = $this->filterMenuByPermissions($verticalMenuData->menu ?? []);
        $horizontalMenuData->menu = $this->filterMenuByPermissions($horizontalMenuData->menu ?? []);

        // Share menuData to the view
        $view->with('menuData', [$verticalMenuData, $horizontalMenuData]);
      }
    });
  }

  /**
   * Filter menu items based on user permissions
   */
  private function filterMenuByPermissions(array $menuItems): array
  {
    $user = Auth::user();
    
    // If user is not authenticated, return empty menu
    if (!$user) {
      return [];
    }

    // Ensure user roles and permissions are loaded
    if (!$user->relationLoaded('roles')) {
      $user->load('roles');
    }

    $filteredMenu = [];
    $pendingHeader = null;

    foreach ($menuItems as $menuItem) {
      // Handle menu headers
      if (isset($menuItem->menuHeader)) {
        // Store the header but don't add it yet
        // We'll add it when we find a visible menu item after it
        $pendingHeader = $menuItem;
        continue;
      }

      // Check if menu item has permission requirement
      $hasPermission = true;
      if (isset($menuItem->permission)) {
        try {
          $hasPermission = $user->can($menuItem->permission);
        } catch (\Exception $e) {
          // If permission check fails, default to false
          $hasPermission = false;
        }
      }

      if (!$hasPermission) {
        continue; // Skip this menu item
      }

      // If we have a pending header and found a visible item, add the header first
      if ($pendingHeader !== null) {
        // Check if header has permission requirement
        $headerHasPermission = true;
        if (isset($pendingHeader->permission)) {
          try {
            $headerHasPermission = $user->can($pendingHeader->permission);
          } catch (\Exception $e) {
            $headerHasPermission = false;
          }
        }
        
        if ($headerHasPermission) {
          $filteredMenu[] = $pendingHeader;
        }
        $pendingHeader = null;
      }

      // If menu item has submenu, filter it recursively
      if (isset($menuItem->submenu) && is_array($menuItem->submenu)) {
        $filteredSubmenu = [];
        foreach ($menuItem->submenu as $submenuItem) {
          if (isset($submenuItem->permission)) {
            try {
              if ($user->can($submenuItem->permission)) {
                $filteredSubmenu[] = $submenuItem;
              }
            } catch (\Exception $e) {
              // Skip this submenu item if permission check fails
            }
          } else {
            // If no permission specified, include it
            $filteredSubmenu[] = $submenuItem;
          }
        }
        
        // Only include parent menu if it has submenu items
        if (count($filteredSubmenu) > 0) {
          $menuItem->submenu = $filteredSubmenu;
          $filteredMenu[] = $menuItem;
        }
      } else {
        // Menu item without submenu
        $filteredMenu[] = $menuItem;
      }
    }

    return $filteredMenu;
  }
}
