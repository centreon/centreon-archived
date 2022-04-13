<?php

namespace Core\Infrastructure\Common\Command\Model\RepositoryTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryInterfaceTemplate;

class WriteRepositoryTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public RepositoryInterfaceTemplate $writeRepositoryInterface,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $databaseVariable = 'db';
        $thisDb = 'this->db';
        $interfaceNamespace = $this->writeRepositoryInterface->namespace . '\\' . $this->writeRepositoryInterface->name;
        $interfaceName = $this->writeRepositoryInterface->name;
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        use Centreon\Infrastructure\DatabaseConnection;
        use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
        use $interfaceNamespace;

        class $this->name extends AbstractRepositoryDRB implements $interfaceName
        {
            /**
             * @param DatabaseConnection $$databaseVariable
             */
            public function __construct(DatabaseConnection $$databaseVariable)
            {
                $$thisDb = $$databaseVariable;
            }
        }

        EOF;

        return $content;
    }
}
