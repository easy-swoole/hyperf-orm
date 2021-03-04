<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace EasySwoole\HyperfOrm;

use Hyperf\Database\Connection;
use Hyperf\Database\Connectors\ConnectionFactory as BaseConnectionFactory;
use InvalidArgumentException;

class ConnectionFactory extends BaseConnectionFactory
{
    /**
     * Create a new connection instance.
     *
     * @param string $driver
     * @param \Closure|\PDO $connection
     * @param string $database
     * @param string $prefix
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Connection
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$driver}]");
    }
}
