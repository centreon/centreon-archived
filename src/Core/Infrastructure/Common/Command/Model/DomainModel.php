<?php

namespace Core\Infrastructure\Common\Command\Model;

class DomainModel
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public bool $exists = false
    ) {
    }
}
