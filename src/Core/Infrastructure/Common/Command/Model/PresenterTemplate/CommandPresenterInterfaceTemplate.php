<?php

namespace Core\Infrastructure\Common\Command\Model\PresenterTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;

class CommandPresenterInterfaceTemplate extends FileTemplate
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
        $dataVariable = '$data';
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
            public function present(mixed $dataVariable): void;
        }

        EOF;

        return $content;
    }

    public function __toString()
    {
        return $this->name;
    }
}
