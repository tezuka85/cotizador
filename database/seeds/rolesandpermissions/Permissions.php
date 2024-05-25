<?php

$permissions = [
    // Dashboard

    [
        'module_id'     => '1',
        'class_method'  => 'index',
        'permissions'   => [
            'title' => 'Dashboard',
            'name' => 'Dashboard'
        ]
    ],
    [
        'module_id'     => '2',
        'class_method'  => 'wizard',
        'permissions'   => [
            'title' => 'Crear nuevo comercio',
            'name' => 'wizard.step1'
        ]
    ],
    [
        'module_id'     => '3',
        'class_method'  => 'index',
        'permissions'   => [
            'title' => 'Menu',
            'name' => 'menu.index'
        ]
    ]
];

return $permissions;