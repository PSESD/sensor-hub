<?php
return [
    'class' => 'canis\auth\identity\providers\Collector',
    'initialItems' => [],
    'handlers' => [
        'Ldap' => [
            'class' => 'canis\auth\identity\providers\Ldap',
        ],
    ],
];
