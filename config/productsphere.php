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
    | Demo Accounts
    |--------------------------------------------------------------------------
    |
    | Sign-in accounts for presenting the demo, one per role. Passwords are
    | read from the environment (e.g. Render's dashboard) and never stored in
    | the repository. When a password is not set, the account still exists as
    | the actor of the demo history, but nobody can sign in with it.
    |
    */

    'demo_accounts' => [
        'admin' => [
            'name' => 'Demo Administrator',
            'email' => env('DEMO_ADMIN_EMAIL', 'admin@productsphere.demo'),
            'password' => env('DEMO_ADMIN_PASSWORD'),
        ],
        'staff' => [
            'name' => 'Demo Staff Member',
            'email' => env('DEMO_STAFF_EMAIL', 'staff@productsphere.demo'),
            'password' => env('DEMO_STAFF_PASSWORD'),
        ],
    ],

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
