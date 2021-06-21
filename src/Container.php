<?php
declare(strict_types=1);

namespace EasySwoole\HyperfOrm;

use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use Psr\Container\ContainerInterface;
use Throwable;

class Container implements ContainerInterface
{
    use Singleton;

    /**
     * @param string $id
     *
     * @return callable|mixed|string|null
     * @throws Throwable
     */
    public function get($id)
    {
        return Di::getInstance()->get($id);
    }

    /**
     * @param string $id
     *
     * @return callable|mixed|string|null
     * @throws Throwable
     */
    public function has($id)
    {
        return Di::getInstance()->get($id) != null;
    }

    public $dependencies = [
        \Hyperf\Contract\LengthAwarePaginatorInterface::class => \Hyperf\Paginator\LengthAwarePaginator::class,
    ];

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return null
     * @throws Throwable
     */
    public function make(string $name, array $parameters = [])
    {
        $data = Di::getInstance($parameters)->get($name);
        if (is_null($data)) {
            // 兼容
            if (interface_exists($name)) {
                if (isset($this->dependencies[$name])) {
                    $name = $this->dependencies[$name];
                } else {
                    return null;
                }
            }
            $parameters = array_values($parameters);
            $data = new $name(...$parameters);
        }
        return $data;
    }
}
