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

    /*
    |--------------------------------------------------------------------------
    | Rwanda Districts
    |--------------------------------------------------------------------------
    |
    | The 30 administrative districts, grouped by province. Used for the
    | optional district field on supply-chain locations.
    |
    */

    'districts' => [
        'Kigali City' => ['Gasabo', 'Kicukiro', 'Nyarugenge'],
        'Eastern Province' => ['Bugesera', 'Gatsibo', 'Kayonza', 'Kirehe', 'Ngoma', 'Nyagatare', 'Rwamagana'],
        'Northern Province' => ['Burera', 'Gakenke', 'Gicumbi', 'Musanze', 'Rulindo'],
        'Southern Province' => ['Gisagara', 'Huye', 'Kamonyi', 'Muhanga', 'Nyamagabe', 'Nyanza', 'Nyaruguru', 'Ruhango'],
        'Western Province' => ['Karongi', 'Ngororero', 'Nyabihu', 'Nyamasheke', 'Rubavu', 'Rusizi', 'Rutsiro'],
    ],

];
