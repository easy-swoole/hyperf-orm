<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/21
 * Time:  12:10 下午
 */

namespace EasySwoole\HyperfOrm;

use EasySwoole\Component\Singleton;
use EasySwoole\Command\CommandManager;
use EasySwoole\HyperfOrm\Command\GenMigrateCommand;
use EasySwoole\HyperfOrm\Command\MigrateCommand;
use EasySwoole\HyperfOrm\Command\MigrateFreshCommand;
use EasySwoole\HyperfOrm\Command\MigrateInstallCommand;
use EasySwoole\HyperfOrm\Command\MigrateRefreshCommand;
use EasySwoole\HyperfOrm\Command\MigrateResetCommand;
use EasySwoole\HyperfOrm\Command\MigrateRollbackCommand;
use EasySwoole\HyperfOrm\Command\MigrateStatusCommand;
use EasySwoole\HyperfOrm\Command\ModelCommand;

class CommandUtility
{
    use Singleton;

    public function init(array $other = [])
    {
        $commands = [
            new ModelCommand(),
            new GenMigrateCommand(),
            new MigrateCommand(),
            new MigrateInstallCommand(),
            new MigrateRollbackCommand(),
            new MigrateResetCommand(),
            new MigrateRefreshCommand(),
            new MigrateFreshCommand(),
            new MigrateStatusCommand(),
        ];
        $commands = array_merge($commands, $other);
        foreach ($commands as $command) {
            CommandManager::getInstance()->addCommand($command);
        }
    }
}