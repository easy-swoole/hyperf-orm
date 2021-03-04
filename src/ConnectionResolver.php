<?php

namespace EasySwoole\HyperfOrm;

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Component\Context\Exception\ModifyError;
use EasySwoole\Pool\Manager;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolver as BaseConnectionResolver;
use Swoole\Coroutine;
use Throwable;

class ConnectionResolver extends BaseConnectionResolver
{
    /**
     * Get a database connection instance.
     *
     * @param string $name
     *
     * @return ConnectionInterface
     * @throws ModifyError
     * @throws Throwable
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $key = sprintf('database.connection.%s', $name);
        $context = ContextManager::getInstance();
        $connection = $context->get($key);
        if (!$connection instanceof ConnectionInterface) {
            $pool = Manager::getInstance()->get($name);
            try {
                $connection = $pool->getObj();
                $context->set($key, $connection);
            } finally {
                if (Coroutine::getUid() > 0) {
                    defer(function () use ($pool, $connection) {
                        $pool->recycleObj($connection);
                    });
                }
            }
        }
        return $connection;
    }
}
