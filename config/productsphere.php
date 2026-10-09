<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Expiry Warning Window
    |--------------------------------------------------------------------------
    |
    | Active batches whose expiry date falls within this many days (inclusive
    | of today) are reported as "approaching expiry" on the dashboard.
    |
    */

    'expiry_warning_days' => (int) env('EXPIRY_WARNING_DAYS', 30),

];
