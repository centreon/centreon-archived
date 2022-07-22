<?php
namespace Centreon\Domain\Entity;

class CfgResourceInstanceRelations
{
    /**
     * Relation with cfg_resource.id
     */
    private ?int $resourceId = null;

    /**
     * Relation with nagios_server.id
     */
    private ?int $instanceId = null;

    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setInstanceId(int $instanceId): void
    {
        $this->instanceId = $instanceId;
    }

    public function getInstanceId(): int
    {
        return $this->instanceId;
    }
}
