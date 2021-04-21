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
use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Migrations\Migrator;
use EasySwoole\EasySwoole\Core;
use Hyperf\Utils\Filesystem\Filesystem;
use EasySwoole\Command\Color;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate';
    }

    protected function migrate()
    {
        // create migreates table
        $this->prepareDatabase();

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        $migrations = $this->migrator
            ->run($this->getMigrationPaths(), [
                'pretend' => CommandManager::getInstance()->getOpt('pretend'),
                'step' => CommandManager::getInstance()->getOpt('step'),
            ]);

        foreach ($migrations as $migration){
            echo Color::info("Migrating: {$migration}") . PHP_EOL;
        }
    }

    public function exec(): ?string
    {
        $coroutine = CommandManager::getInstance()->getOpt('coroutine', false);
        if ($coroutine) {
            $this->migrate();
        } else {
            $scheduler = new Scheduler();
            $scheduler->add(function () {
                $this->migrate();
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
        return 'Run the database migrations';
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $database = CommandManager::getInstance()->getOpt('database', 'default');
        $this->migrator->setConnection($database);

        if (!$this->migrator->repositoryExists()) {
            $caller = new Caller();
            $caller->setParams([
                'easyswoole',
                'migrate:install',
                "--database={$database}",
                "--coroutine=true",
            ]);
            $caller->setScript('easyswoole');
            $caller->setCommand('migrate:install');
            CommandManager::getInstance()->run($caller);
        }
    }
}