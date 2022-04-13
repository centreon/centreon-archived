<?php

namespace Core\Infrastructure\Common\Command\Model\RepositoryTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;

class RepositoryInterfaceTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        interface $this->name
        {
        }

        EOF;

        return $content;
    }
}
