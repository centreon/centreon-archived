<?php

namespace Core\Infrastructure\Common\Command\Model\FactoryTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;

class FactoryTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public ModelTemplate $modelTemplate,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $modelName = $this->modelTemplate->name;
        $modelNamespace = $this->modelTemplate->namespace . '\\' . $this->modelTemplate->name;

        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        use $modelNamespace;

        class $this->name
        {
            public static function create(): $modelName
            {
                return new $modelName();
            }
        }

        EOF;

        return $content;
    }
}