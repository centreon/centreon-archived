<?php

namespace Core\Infrastructure\Common\Command\Model\PresenterTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;

class QueryPresenterTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public QueryPresenterInterfaceTemplate $presenterInterface,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $interfaceNamespace = $this->presenterInterface->namespace . '\\' . $this->presenterInterface->name;
        $interfaceName = $this->presenterInterface->name;
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        use $interfaceNamespace;

        class $this->name extends $interfaceName
        {
            /**
             * @inheritDoc
             */
            public function present(): void
            {
            }
        }

        EOF;

        return $content;
    }
}
