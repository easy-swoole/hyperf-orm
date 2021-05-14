<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  10:24 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateRefreshCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:refresh';
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Reset and re-run all migrations';
    }

    public function exec(): ?string
    {
        $this->initialize();
        $scheduler = new Scheduler();
        $scheduler->add(function () {
            // Next we'll gather some of the options so that we can have the right options
            // to pass to the commands. This includes options such as which database to
            // use and the path to use for the migration. Then we'll run the command.
            $connection = CommandManager::getInstance()->getOpt('database', 'default');
            $path = CommandManager::getInstance()->getOpt('path', '');

            // If the "step" option is specified it means we only want to rollback a small
            // number of migrations before migrating again. For example, the user might
            // only rollback and remigrate the latest four migrations instead of all.
            $step = (int)CommandManager::getInstance()->getOpt('step', 0);

            if ($step > 0) {
                $this->runRollback($connection, $path, $step);
            } else {
                $this->runReset($connection, $path);
            }

            // The refresh command is essentially just a brief aggregate of a few other of
            // the migration commands and just provides a convenient wrapper to execute
            // them in succession. We'll also see if we need to re-seed the database.
            $caller = new Caller();
            $params = [
                'easyswoole',
                'migrate',
                "--database={$connection}",
                "--force=true",
                "--coroutine=true"
            ];
            if (!empty($path)) {
                $params[] = "--path={$path}";
            }
            $realpath = CommandManager::getInstance()->getOpt('realpath', '');
            if (!empty($realpath)) {
                $params[] = "--realpath={$realpath}";
            }
            $caller->setParams($params);
            $caller->setScript('easyswoole');
            $caller->setCommand('migrate');
            CommandManager::getInstance()->run($caller);

            Timer::clearAll();
        });
        $scheduler->start();
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

    /**
     * Run the rollback command.
     */
    protected function runRollback(string $database, string $path, int $step): void
    {
        $caller = new Caller();
        $params = [
            'easyswoole',
            'migrate:rollback',
            "--database={$database}",
            "--force=true",
            "--coroutine=true"
        ];
        if (!empty($path)) {
            $params[] = "--path={$path}";
        }
        $realpath = CommandManager::getInstance()->getOpt('realpath', '');
        if (!empty($realpath)) {
            $params[] = "--realpath={$realpath}";
        }
        if ($step > 0) {
            $params[] = "--step={$step}";
        }
        $caller->setParams($params);
        $caller->setScript('easyswoole');
        $caller->setCommand('migrate:rollback');
        CommandManager::getInstance()->run($caller);
    }

    /**
     * Run the reset command.
     */
    protected function runReset(string $database, string $path): void
    {
        $caller = new Caller();
        $params = [
            'easyswoole',
            'migrate:reset',
            "--database={$database}",
            "--force=true",
            "--coroutine=true"
        ];
        if (!empty($path)) {
            $params[] = "--path={$path}";
        }
        $realpath = CommandManager::getInstance()->getOpt('realpath', '');
        if (!empty($realpath)) {
            $params[] = "--realpath={$realpath}";
        }
        $caller->setParams($params);
        $caller->setScript('easyswoole');
        $caller->setCommand('migrate:reset');
        CommandManager::getInstance()->run($caller);
    }

}