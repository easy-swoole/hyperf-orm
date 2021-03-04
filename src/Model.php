<?php

namespace EasySwoole\HyperfOrm;

use EasySwoole\Component\Di;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model as BaseModel;

class Model extends BaseModel
{
    public function getConnection(): ConnectionInterface
    {
        return Di::getInstance()->get(ConnectionResolverInterface::class)->connection($this->getConnectionName());
    }
}
