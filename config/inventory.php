<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager Email
    |--------------------------------------------------------------------------
    |
    | This value is the email address of the person responsible for managing
    | inventory. Low stock alerts will be sent to this email address.
    |
    */
    'manager_email' => env('INVENTORY_MANAGER_EMAIL', 'inventory@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Stock Level Thresholds
    |--------------------------------------------------------------------------
    |
    | These values determine the thresholds for stock status calculations.
    | Warning level is calculated as min_stock_level * warning_multiplier
    |
    */
    'warning_multiplier' => 1.5,
];
