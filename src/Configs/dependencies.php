<?php

use Psr\Container\ContainerInterface;
use EasySwoole\HyperfOrm\Container;
use EasySwoole\HyperfOrm\ConfigFactory;
use EasySwoole\HyperfOrm\ConnectionResolver;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;

return [
    ContainerInterface::class => Container::class,
    ConfigInterface::class => ConfigFactory::class,
    ConnectionResolverInterface::class => [
        'key' => ConnectionResolverInterface::class,
        "obj" => ConnectionResolver::class,
        "arg" => [[]],
    ],
];
