<p align="center">
    <a href="https://www.easyswoole.com/" target="_blank">
        <img src="https://raw.githubusercontent.com/easy-swoole/easyswoole/3.x/easyswoole.png" height="100px">
    </a>
    <h1 align="center">EasySwoole Hyperf Orm </h1>
    <br>
</p>

Install
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require easyswoole/hyperf-orm "dev-main"
```

or add

```
"easyswoole/hyperf-orm": "*"
```
to the require section of your `composer.json` file.

Config
------------
```php
file dev.php add

<?php
    return  [
            'databases' => [
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
            ]
    ];
```

DI
------------
file EasySwooleEvent.php add
```php
    <?php
    
    use Psr\Container\ContainerInterface;
    use EasySwoole\HyperfOrm\Container;
    use EasySwoole\HyperfOrm\ConfigFactory;
    use EasySwoole\HyperfOrm\ConnectionResolver;
    use Hyperf\Contract\ConfigInterface;
    use Hyperf\Database\ConnectionResolverInterface;
    use EasySwoole\Component\Di;
    
    Di::getInstance()->set(ContainerInterface::class, Container::class);
    Di::getInstance()->set(ConfigInterface::class, ConfigFactory::class);
    Di::getInstance()->set(ConnectionResolverInterface::class,  ConnectionResolver::class, []);
```

Command Config
----------------

file bootstrap.php add

```php
<?php
//全局bootstrap事件
use EasySwoole\Command\CommandManager;
use EasySwoole\HyperfOrm\Command\ModelCommand;

// command
CommandManager::getInstance()->addCommand(new ModelCommand());
```
    
Command 
---------------- 

```
    php easyswoole gen:model 

    or 

    php easyswoole gen:model tableName
```
    

Model
-------------

```php
<?php

declare (strict_types=1);
namespace App\Model;

use EasySwoole\HyperfOrm\Model;
/**
 * @property string $demo_id 
 * @property int $create_at 
 * @property int $update_at 
 */
class Demo extends Model
{
    /**
     * primaryKey
     *
     * @var string
     */
    protected $primaryKey = 'demo_id ';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'demo';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['demo_id', 'create_at', 'update_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['demo_id' => 'string', 'create_at' => 'datetime', 'update_at' => 'datetime'];
}
```

Use
------

file EasySwooleEvent.php add

```php
<?php
    ....

    use EasySwoole\EasySwoole\Config;        
    use EasySwoole\Pool\Manager;
    use EasySwoole\HyperfOrm\MysqlPool;        
    ....

    public static function initialize() {

        $databases = Config::getInstance()->getConf('databases');
        $manager = Manager::getInstance();
        foreach ($databases as $name => $conf) {
            if (!is_null($manager->get($name))) {
                continue;
            }
            Manager::getInstance()->register(new MysqlPool($conf), $name);
        }
    }
        
```
