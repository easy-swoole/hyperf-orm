<?php
/**
 * User: XueSi
 * Date: 2021/2/23 18:11
 * Author: XueSi <1592328848@qq.com>
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Component\Di;
use EasySwoole\HyperfOrm\Generate\GenerateModel;
use Psr\Container\ContainerInterface;
use Swoole\Timer;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Scheduler;
use EasySwoole\Command\Color;

class ModelCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'gen:model';
    }

    public function exec(): ?string
    {
        $args = CommandManager::getInstance()->getArgs();
        $table = !empty($args) ? current($args) : '';
        Core::getInstance()->initialize();
        $scheduler = new Scheduler();
        $scheduler->add(function () use ($table) {
            try {
                $container = Di::getInstance()->get(ContainerInterface::class);
                $model = new GenerateModel($container);
                $model->create($table);
                echo Color::info('success');
            } catch (\Throwable $exception) {
                echo Color::error("false: {$exception->getMessage()}");
            }
            Timer::clearAll();
        });
        $scheduler->start();
        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('table', 'table name');
        return $commandHelp;
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'generate model!';
    }
}
