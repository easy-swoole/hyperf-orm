<?php
/**
 * Created by PhpStorm.
 * User:  ice
 * Email: xykxyk2008@163.com
 * Date:  2021/4/20
 * Time:  9:19 下午
 */

namespace EasySwoole\HyperfOrm\Command;

use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Core;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Filesystem\Filesystem;

abstract class BaseCommand
{
    /**
     * @var DatabaseMigrationRepository|mixed
     */
    protected $repository;

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var ConnectionResolverInterface|mixed
     */
    protected $resolver;

    /**
     * @var bool
     */
    private $isInitialize = false;

    protected function initialize()
    {
        if ($this->isInitialize) {
            return;
        }
        $this->isInitialize = true;
        Core::getInstance()->initialize();
        $this->resolver = make(ConnectionResolverInterface::class);
        $this->repository = make(DatabaseMigrationRepository::class, [$this->resolver, 'migrations']);
        $this->migrator = make(Migrator::class, [
            $this->repository,
            $this->resolver,
            make(Filesystem::class),
        ]);
    }

    /**
     * Get all of the migration paths.
     */
    protected function getMigrationPaths(): array
    {
        // Here, we will check to see if a path option has been defined. If it has we will
        // use the path relative to the root of the installation folder so our database
        // migrations may be run for any customized path from within the application.

        $path = CommandManager::getInstance()->getOpt('path', false);
        if ($path) {
            $collect = new Collection($path);
            return $collect->map(function ($path) {
                return ! $this->usingRealPath()
                    ? EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . $path
                    : $path;
            })->all();
        }

        return array_merge(
            $this->migrator->paths(),
            [$this->getMigrationPath()]
        );
    }

    /**
     * Determine if the given path(s) are pre-resolved "real" paths.
     *
     * @return bool
     */
    protected function usingRealPath()
    {
        return CommandManager::getInstance()->getOpt('realpath', false);
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'migrations';
    }
}