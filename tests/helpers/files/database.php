<?php

return [
    [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST'),
        'database' => getenv('DB_DATABASE'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'name' => 'default',
    ], [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST_2'),
        'database' => getenv('DB_DATABASE_2'),
        'username' => getenv('DB_USERNAME_2'),
        'password' => getenv('DB_PASSWORD_2'),
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'name' => 'another_connection_name',
    ],
];
