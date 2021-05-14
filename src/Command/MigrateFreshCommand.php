<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  10:45 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Caller;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateFreshCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:fresh';
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Drop all tables and re-run all migrations';
    }

    protected function fresh()
    {
        $connection = CommandManager::getInstance()->getOpt('database', 'default');
        $dropViews = CommandManager::getInstance()->getOpt('drop-views', false);
        if ($dropViews) {
            $this->dropAllViews($connection);
            echo Color::info("Dropped all views successfully.") . PHP_EOL;
        }

        $this->dropAllTables($connection);
        echo Color::info("Dropped all tables successfully.") . PHP_EOL;

        $caller = new Caller();
        $params = [
            'easyswoole',
            'migrate',
            "--database={$connection}",
            "--force=true",
            "--coroutine=true",
        ];
        if (!empty($path)) {
            $params[] = "--path={$path}";
        }
        $realpath = CommandManager::getInstance()->getOpt('realpath', '');
        if (!empty($realpath)) {
            $params[] = "--realpath={$realpath}";
        }
        $step = CommandManager::getInstance()->getOpt('step');
        if ($step) {
            $params[] = "--step={$step}";
        }
        $caller->setParams($params);
        $caller->setScript('easyswoole');
        $caller->setCommand('migrate');
        CommandManager::getInstance()->run($caller);
    }

    public function exec(): ?string
    {
        $this->initialize();
        $coroutine = CommandManager::getInstance()->getOpt('coroutine', false);
        if ($coroutine) {
            $this->fresh();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () {
                $this->fresh();
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
        $commandHelp->addActionOpt('drop-views', 'Drop all tables and views');
        return $commandHelp;
    }

    /**
     * Drop all of the database views.
     */
    protected function dropAllViews(string $connection)
    {
        $this->resolver->connection($connection)->getSchemaBuilder()->dropAllViews();
    }

    /**
     * Drop all of the database tables.
     */
    protected function dropAllTables(string $connection)
    {
        $this->resolver->connection($connection)->getSchemaBuilder()->dropAllTables();
    }
}