<?php

namespace Core\Infrastructure\Common\Command\Model\PresenterTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;

class QueryPresenterInterfaceTemplate extends FileTemplate
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

        use Core\Application\Common\UseCase\PresenterInterface;

        interface $this->name extends PresenterInterface
        {
            /**
             * Present no content.
             */
            public function present(): void;
        }

        EOF;

        return $content;
    }
}
