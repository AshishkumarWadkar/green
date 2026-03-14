<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Follow-ups
    |--------------------------------------------------------------------------
    |
    | Maximum number of follow-ups allowed per enquiry. This is configurable
    | from the admin panel but defaults to 3.
    |
    */
    'max_follow_ups' => env('ENQUIRY_MAX_FOLLOW_UPS', 3),

    /*
    |--------------------------------------------------------------------------
    | Follow-up Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Settings for follow-up reminders and notifications.
    |
    */
    'reminder' => [
        'before_hours' => env('ENQUIRY_REMINDER_BEFORE_HOURS', 2),
        'overdue_alert' => env('ENQUIRY_OVERDUE_ALERT', true),
    ],
];
