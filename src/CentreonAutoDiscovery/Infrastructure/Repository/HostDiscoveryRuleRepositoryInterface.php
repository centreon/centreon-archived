<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace CentreonAutoDiscovery\Infrastructure\Repository;

interface HostDiscoveryRuleRepositoryInterface
{
    /**
     * Install provider
     *
     * @param int $pluginPackId
     * @param int $typeId the provider type id
     * @param array $rule
     * @param integer $commandId
     * @return integer
     */
    public function installProvider(int $pluginPackId, int $typeId, array $rule, int $commandId): int;

    /**
     * Install provider mapping (attributes, association, templates...)
     *
     * @param integer $providerId
     * @param string $name
     * @param array $mapping
     * @return integer
     */
    public function installProviderMapping(int $providerId, string $name, array $mapping): int;

    /**
     * Update provider
     *
     * @param int $providerId
     * @param int $pluginPackId
     * @param int $typeId the provider type id
     * @param array $rule
     * @param integer $commandId
     * @return void
     */
    public function updateProvider(int $providerId, int $pluginPackId, int $typeId, array $rule, int $commandId): void;

    /**
     * Update provider mapping (attributes, association, templates...)
     *
     * @param integer $providerId
     * @param string $name
     * @param array $mapping
     * @return void
     */
    public function updateProviderMapping(int $providerId, string $name, array $mapping): void;

    /**
     * Install provider type (eg: vmware, aws, ...)
     *
     * @param string $name Provider type
     * @return void
     */
    public function installProviderType(string $name): void;

    /**
     * Remove provider
     *
     * @param int $pluginPackId
     * @return void
     */
    public function removeProvider(int $pluginPackId);
}
