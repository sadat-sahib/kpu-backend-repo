<?php
return [

    'employee' => [
        'email'    => env('DEFAULT_EMPLOYEE_EMAIL', 'admin@gmail.com'),
        'password' => env('DEFAULT_EMPLOYEE_PASSWORD', 'password'),
        'role'     => 'admin',
        'type'     => 'employee',
    ],

    'assistant' => [
        'email'    => env('DEFAULT_ASSISTANT_EMAIL', 'assistant@gmail.com'),
        'password' => env('DEFAULT_ASSISTANT_PASSWORD', 'password'),
        'role'     => null,
        'type'     => 'assistant',
    ],

];
