<?php

// config/jobs.php

return [
    /*
    |--------------------------------------------------------------------------
    | Job Status System Mappings
    |--------------------------------------------------------------------------
    |
    | Maps semantic business terms (like 'active', 'inactive') to the literal
    | string values stored inside your database 'status' column.
    |
    */
    'status_map' => [
        'active'   => 'pending',
        'inactive' => 'completed',
    ],
];