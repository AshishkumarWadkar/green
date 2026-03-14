<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reporting hierarchy: role name => role name that this role reports to
    |--------------------------------------------------------------------------
    | Manager reports to Admin. Sales Executive reports to Manager. Admin has no superior.
    */
    'reports_to_role' => [
        'Manager' => 'Admin',
        'Sales Executive' => 'Manager',
    ],
];
