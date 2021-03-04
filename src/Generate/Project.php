<?php

namespace EasySwoole\HyperfOrm\Generate;

use Hyperf\Utils\CodeGen\Project as BaseProject;

class Project extends BaseProject
{
    private $json;

    protected function getAutoloadRules(): array
    {
        if (!$this->json) {
            $path = EASYSWOOLE_ROOT . '/composer.json';
            if (!is_readable($path)) {
                throw new \RuntimeException('composer.json is not readable.');
            }
            $this->json = collect(json_decode(file_get_contents($path), true));
        }
        return data_get($this->json, 'autoload.psr-4', []);
    }
}
