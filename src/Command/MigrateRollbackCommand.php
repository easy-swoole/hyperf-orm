<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  10:07 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateRollbackCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:rollback';
    }

    protected function rollback()
    {
        $database = CommandManager::getInstance()->getOpt('database', 'default');
        $this->migrator->setConnection($database);
        $rolledBacks = $this->migrator->rollback($this->getMigrationPaths(), [
            'pretend' => CommandManager::getInstance()->getOpt('pretend'),
            'step'    => CommandManager::getInstance()->getOpt('step'),
        ]);
        if (!empty($rolledBacks)) {
            foreach ($rolledBacks as $rolledBack) {
                echo Color::info("Rolled back: {$rolledBack}") . PHP_EOL;
            }
        } else {
            echo Color::error("Migration not found.") . PHP_EOL;
        }
    }

    public function exec(): ?string
    {
        $coroutine = CommandManager::getInstance()->getOpt('coroutine', false);
        if ($coroutine) {
            $this->rollback();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () {
                $this->rollback();
                Timer::clearAll();
            });
            $scheduler->start();
        }

        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('database', 'The database connection to use');
        $commandHelp->addActionOpt('force', 'Force the operation to run when in production');
        $commandHelp->addActionOpt('path', 'The path to the migrations files to be executed');
        $commandHelp->addActionOpt('realpath', 'Indicate any provided migration file paths are pre-resolved absolute paths');
        $commandHelp->addActionOpt('pretend', 'Dump the SQL queries that would be run');
        $commandHelp->addActionOpt('step', 'Force the migrations to be run so they can be rolled back individually');

        return $commandHelp;
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Rollback the last database migration';
    }
}