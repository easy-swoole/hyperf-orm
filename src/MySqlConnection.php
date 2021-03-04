<?php

namespace EasySwoole\HyperfOrm;

use Hyperf\Database\MySqlConnection as BaseMySqlConnection;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

class MySqlConnection extends BaseMySqlConnection
{
    public function bindValues(\PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value
            );
        }
    }

    /**
     * Log a query in the connection's query log.
     * @param null|array|int|\Throwable $result
     */
    public function logQuery(string $query, array $bindings, ?float $time = null, $result = null)
    {
        parent::logQuery($query, $bindings, $time, $result);
        if (! Arr::isAssoc($bindings)) {
            foreach ($bindings as $key => $value) {
                $query = Str::replaceFirst('?', "'{$value}'", $query);
            }
        }
        if (Arr::get($this->config, 'showSql', false)) {
            echo $query . PHP_EOL;
        }
    }
}
