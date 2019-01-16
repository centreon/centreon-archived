<?php
namespace CentreonModule\Infrastructure\Source;

use CentreonModule\Infrastructure\Entity\Module;

interface SourceInterface
{

    public function getList(string $search = null, bool $installed = null, bool $updated = null);

    public function createEntityFromConfig(string $configFile): Module;

    public function isEligible(Module $entity, string $search = null, bool $installed = null, bool $updated = null): bool;
}
