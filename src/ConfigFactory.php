<?php

namespace EasySwoole\HyperfOrm;

use Hyperf\Contract\ConfigInterface;
use EasySwoole\EasySwoole\Config;

class ConfigFactory implements ConfigInterface
{

    public function get(string $key, $default = null)
    {
        $config = Config::getInstance()->getConf($key);
        return $config ?? $default;
    }

    public function has(string $keys)
    {
        return !empty($this->get($keys));
    }

    public function set(string $key, $value)
    {
        Config::getInstance()->setConf($key, $value);
    }
}
