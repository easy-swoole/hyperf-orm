<?php

namespace EasySwoole\HyperfOrm;

use EasySwoole\Component\Di;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Pool\Config;
use Hyperf\Database\Connection;
use Psr\Container\ContainerInterface;

class MysqlPool extends AbstractPool
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ConnectionFactory
     */
    private $factory;

    public function __construct(array $config)
    {
        $this->config = $config;

        $container = Di::getInstance()->get(ContainerInterface::class);
        $this->factory = new ConnectionFactory($container);

        $conf = new Config($config['pool']);
        parent::__construct($conf);
    }

    protected function createObject()
    {
        $connection = $this->factory->make($this->config);
        if ($connection instanceof Connection) {
            $connection->setReconnector(function ($connection) {
                if ($connection instanceof Connection) {
                    $this->refresh($connection);
                }
            });
        }
        return $connection;
    }

    protected function refresh(Connection $connection)
    {
        $refresh = $this->factory->make($this->config);
        if ($refresh instanceof Connection) {
            $connection->disconnect();
            $connection->setPdo($refresh->getPdo());
            $connection->setReadPdo($refresh->getReadPdo());
        }
    }
}
