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

    public function init()
    {
        CommandManager::getInstance()->addCommand(new ModelCommand());
        CommandManager::getInstance()->addCommand(new GenMigrateCommand());
        CommandManager::getInstance()->addCommand(new MigrateCommand());
        CommandManager::getInstance()->addCommand(new MigrateInstallCommand());
        CommandManager::getInstance()->addCommand(new MigrateRollbackCommand());
        CommandManager::getInstance()->addCommand(new MigrateResetCommand());
        CommandManager::getInstance()->addCommand(new MigrateRefreshCommand());
        CommandManager::getInstance()->addCommand(new MigrateFreshCommand());
        CommandManager::getInstance()->addCommand(new MigrateStatusCommand());
    }
}