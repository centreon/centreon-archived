<?php
namespace Centreon\Domain\Entity;

class CfgResourceInstanceRelations
{
    /**
     * Relation with cfg_resource.id
     *
     * @var int
     */
    private $resourceId;

    /**
     * Relation with nagios_server.id
     *
     * @var int
     */
    private $instanceId;

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
