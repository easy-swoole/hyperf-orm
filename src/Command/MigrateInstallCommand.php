<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  3:47 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Command\Color;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateInstallCommand implements CommandInterface
{
    protected $repository;

    public function __construct()
    {
        Core::getInstance()->initialize();
        $resolver = make(ConnectionResolverInterface::class);
        $this->repository = make(DatabaseMigrationRepository::class, [$resolver, 'migrations']);;
    }

    public function commandName(): string
    {
        return 'migrate:install';
    }

    protected function install(): string
    {
        $this->repository->setSource(CommandManager::getInstance()->getOpt('database', 'default'));
        $this->repository->createRepository();
        return Color::info("Migration table created successfully.");
    }

    public function exec(): ?string
    {
        if (Coroutine::getUid()) {
            $message = $this->install();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () use (&$message) {
                $message = $this->install();
                Timer::clearAll();
            });
            $scheduler->start();
        }
        return $message;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('database', 'The database connection to use');
        return $commandHelp;
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Create the migration repository';
    }
}