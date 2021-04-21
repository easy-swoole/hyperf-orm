<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  10:18 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateResetCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:reset';
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Rollback all database migrations';
    }

    protected function reset()
    {
        $database = CommandManager::getInstance()->getOpt('database', 'default');
        $this->migrator->setConnection($database);
        // First, we'll make sure that the migration table actually exists before we
        // start trying to rollback and re-run all of the migrations. If it's not
        // present we'll just bail out with an info message for the developers.
        if (! $this->migrator->repositoryExists()) {
            echo Color::error("Migration table not found.") . PHP_EOL;
        } else {
            $resets = $this->migrator->reset(
                $this->getMigrationPaths(),
                (bool)CommandManager::getInstance()->getOpt('pretend')
            );
            if (!empty($resets)) {
                foreach ($resets as $reset) {
                    echo Color::info("Rolled back: {$reset}") . PHP_EOL;
                }
            } else {
                echo Color::error("Nothing to rollback.") . PHP_EOL;
            }
        }
    }

    public function exec(): ?string
    {
        $coroutine = CommandManager::getInstance()->getOpt('coroutine', false);
        if ($coroutine) {
            $this->reset();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () {
                $this->reset();
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

        return $commandHelp;
    }
}