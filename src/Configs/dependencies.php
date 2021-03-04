<?php

use EasySwoole\HyperfOrm\ConfigFactory;
use EasySwoole\HyperfOrm\ConnectionResolver;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;

return [
    ConfigInterface::class => ConfigFactory::class,
    [
        'key' => ConnectionResolverInterface::class,
        "obj" => ConnectionResolver::class,
        "arg" => [[]],
    ],
];
