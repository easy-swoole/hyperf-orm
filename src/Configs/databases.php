<?php
return [
    'default' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'port'      => 3306,
        'database'  => 'easysoole',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => 't_',
        'showSql'   => false,
        'pool'      => [
            'intervalCheckTime' => 15 * 1000,
            'maxIdleTime'       => 60,
            'maxObjectNum'      => 15,
            'minObjectNum'      => 1,
            'getObjectTimeout'  => 3.0,
        ],
        'commands'  => [
            'gen:model' => [
                'path'        => 'App/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses'        => 'EasySwoole\HyperfOrm\Model',
                'refresh_fillable' => true,
                'with_comments' => true,
            ],
        ],
        'options'   => [
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ],
];
