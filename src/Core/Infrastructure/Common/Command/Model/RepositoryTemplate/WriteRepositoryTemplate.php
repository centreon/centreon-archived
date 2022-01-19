<?php

namespace Core\Infrastructure\Common\Command\Model\RepositoryTemplate;

class WriteRepositoryTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public bool $exists = false
    ) {
    }
}
