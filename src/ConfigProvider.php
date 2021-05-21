<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace EasySwoole\HyperfOrm;

use Psr\Container\ContainerInterface;
use EasySwoole\HyperfOrm\Container;
use EasySwoole\HyperfOrm\ConfigFactory;
use EasySwoole\HyperfOrm\ConnectionResolver;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ContainerInterface::class          => Container::class,
                ConfigInterface::class             => ConfigFactory::class,
                ConnectionResolverInterface::class => [
                    'key' => ConnectionResolverInterface::class,
                    "obj" => ConnectionResolver::class,
                    "arg" => [[]],
                ],
            ],
            'publish'      => [
                [
                    'id'          => 'databases',
                    'description' => 'The config for databases.',
                    'source'      => __DIR__ . '/Configs/databases.php',
                    'destination' => EASYSWOOLE_ROOT . '/App/Configs/databases.php',
                ],
            ],
        ];
    }
}
