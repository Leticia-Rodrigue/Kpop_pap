<?php
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'k-popuniverse',
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'security' => 'tls',
        'username' => 'conta@example.com',
        'password' => 'app-password-ou-token',
        'from_email' => 'conta@example.com',
        'from_name' => 'K-Pop Universe',
        'admin_email' => 'conta@example.com',
    ],
    'app' => [
        'base_url' => 'http://localhost/Kpop_pap',
        'reset_token_ttl_minutes' => 30,
    ],
];
