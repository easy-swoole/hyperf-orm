<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  10:54 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Utility\ArrayToTextTable;
use Hyperf\Utils\Collection;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class MigrateStatusCommand extends BaseCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'migrate:status';
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Show the status of each migration';
    }

    public function exec(): ?string
    {
        $scheduler = new Scheduler();
        $scheduler->add(function () {

            $database = CommandManager::getInstance()->getOpt('database', 'default');
            $this->migrator->setConnection($database);

            if (!$this->migrator->repositoryExists()) {
                echo Color::error("Migration table not found.") . PHP_EOL;
            } else {
                $ran = $this->migrator->getRepository()->getRan();
                $batches = $this->migrator->getRepository()->getMigrationBatches();
                if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
                    echo new ArrayToTextTable($migrations);
                } else {
                    echo Color::error('No migrations found') . PHP_EOL;
                }
            }
            Timer::clearAll();
        });
        $scheduler->start();
        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('database', 'The database connection to use');
        $commandHelp->addActionOpt('path', 'The path to the migrations files to be executed');
        $commandHelp->addActionOpt('realpath', 'Indicate any provided migration file paths are pre-resolved absolute paths');
        return $commandHelp;
    }

    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())->map(function ($migration) use ($ran, $batches) {
            $migrationName = $this->migrator->getMigrationName($migration);
            return [
                'Ran?'      => in_array($migrationName, $ran) ? "Yes" : "No",
                'Migration' => $migrationName,
                'Batch'     => $batches[$migrationName] ?? '',
            ];
        })->toArray();
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles()
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }
}