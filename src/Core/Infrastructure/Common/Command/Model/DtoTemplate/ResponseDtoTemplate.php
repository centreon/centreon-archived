<?php

namespace Core\Infrastructure\Common\Command\Model\DtoTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;

class ResponseDtoTemplate extends FileTemplate
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

        class $this->name
        {
        }

        EOF;

        return $content;
    }

    public function __toString()
    {
        return $this->name;
    }
}
