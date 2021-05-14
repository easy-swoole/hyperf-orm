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
use EasySwoole\Command\Color;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateInstallCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:install';
    }

    protected function install()
    {
        $database = CommandManager::getInstance()->getOpt('database', 'default');
        $this->migrator->setConnection($database);
        if (!$this->migrator->repositoryExists()) {
            $this->repository->setSource($database);
            $this->repository->createRepository();
            echo Color::info("Migration table created successfully.");
        }
    }

    public function exec(): ?string
    {
        $this->initialize();
        $coroutine = CommandManager::getInstance()->getOpt('coroutine', false);
        if ($coroutine) {
            $this->install();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () {
                $this->install();
                Timer::clearAll();
            });
            $scheduler->start();
        }
        return null;
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