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
use Swoole\Timer;
use Hyperf\Database\Commands\Migrations\TableGuesser;
use Hyperf\Database\Migrations\MigrationCreator;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Str;
use Swoole\Coroutine\Scheduler;
use Throwable;
use EasySwoole\Command\Color;

class GenMigrateCommand extends BaseCommand implements CommandInterface
{

    public function commandName(): string
    {
        return 'gen:migrate';
    }

    public function exec(): ?string
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $args = CommandManager::getInstance()->getArgs();
        $name = !empty($args) ? current($args) : '';
        $name = Str::snake(trim($name));
        if (!$name) {
            return Color::error('Created Migration Fail: Name Not Empty') . PHP_EOL;
        }

        $table = CommandManager::getInstance()->getOpt('table');
        $create = CommandManager::getInstance()->getOpt('create', false);

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (!$table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (!$table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.

        $scheduler = new Scheduler();
        $scheduler->add(function () use ($name, $table, $create, &$message) {
            $message = $this->writeMigration($name, $table, $create);
            Timer::clearAll();
        });
        $scheduler->start();
        return $message;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('name', 'The name of the migration');
        $commandHelp->addActionOpt('--create', 'The table to be created');
        $commandHelp->addActionOpt('--table', 'The table to migrate');
        $commandHelp->addActionOpt('--path', 'The location where the migration file should be created');
        $commandHelp->addActionOpt('--realpath', 'Indicate any provided migration file paths are pre-resolved absolute paths');
        return $commandHelp;
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'Generate a new migration file!';
    }

    protected function writeMigration(string $name, ?string $table, bool $create): string
    {
        $creator = make(MigrationCreator::class, [make(Filesystem::class)]);
        try {
            $file = pathinfo($creator->create($name, $this->getMigrationPath(), $table, $create), PATHINFO_FILENAME);
            return  Color::info("Created Migration: {$file}");
        } catch (Throwable $e) {
            return  Color::error("Created Migration Fail: {$e->getMessage()}");
        }
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (!is_null($targetPath = CommandManager::getInstance()->getOpt('path'))) {
            return !$this->usingRealPath() ? EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . $targetPath : $targetPath;
        }

        return parent::getMigrationPath();
    }
}