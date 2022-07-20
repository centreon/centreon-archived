<?php

/*
 * Copyright 2005-2022 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

class CentreonConfigurationChange
{
    public const UNKNOWN_RESOURCE_TYPE = 'Unknown resource type';
    public const RESOURCE_TYPE_HOST = 'host';
    public const RESOURCE_TYPE_HOSTGROUP = 'hostgroup';
    public const RESOURCE_TYPE_SERVICE = 'service';
    public const RESOURCE_TYPE_SERVICEGROUP = 'servicegroup';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(
        private \CentreonDB $db
    ) {
    }

    /**
     * Return ids of hosts linked to hostgroups
     *
     * @param int[] $hostgroupIds
     * @param bool $shouldHostgroupBeEnabled (default true)
     * @return int[]
     * @throws \Exception
     */
    public function findHostsForConfigChangeFlagFromHostGroupIds(
        array $hostgroupIds,
        bool $shouldHostgroupBeEnabled = true
    ): array {
        if (empty($hostgroupIds)) {
            return [];
        }

        $bindedParams = [];
        foreach ($hostgroupIds as $key => $hostgroupId) {
            $bindedParams[':hostgroup_id_' . $key] = $hostgroupId;
        }

        if ($shouldHostgroupBeEnabled) {
            $query = "SELECT DISTINCT(hgr.host_host_id)
                FROM hostgroup_relation hgr
                JOIN hostgroup ON hostgroup.hg_id = hgr.hostgroup_hg_id
                WHERE hostgroup.hg_activate = '1'
                AND hgr.hostgroup_hg_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
        } else {
            $query = "SELECT DISTINCT(hgr.host_host_id) FROM hostgroup_relation hgr
                WHERE hgr.hostgroup_hg_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
        }

        $stmt = $this->db->prepare($query);
        foreach ($bindedParams as $bindedParam => $bindedValue) {
            $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Return ids of hosts linked to services
     *
     * @param int[] $serviceIds
     * @param bool $shouldServiceBeEnabled (default true)
     * @return int[]
     * @throws \Exception
     */
    public function findHostsForConfigChangeFlagFromServiceIds(
        array $serviceIds,
        bool $shoudlServiceBeEnabled = true
    ): array {
        if (empty($serviceIds)) {
            return [];
        }

        $bindedParams = [];
        foreach ($serviceIds as $key => $serviceId) {
            $bindedParams[':service_id_' . $key] = $serviceId;
        }

        if ($shoudlServiceBeEnabled) {
            $query = "SELECT DISTINCT(hsr.host_host_id)
                FROM host_service_relation hsr
                JOIN service ON service.service_id = hsr.service_service_id
                WHERE service.service_activate = '1' AND hsr.service_service_id IN ("
                . implode(', ', array_keys($bindedParams)) . ")";
        } else {
            $query = "SELECT DISTINCT(hsr.host_host_id)
                FROM host_service_relation hsr
                WHERE hsr.service_service_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
        }

        $stmt = $this->db->prepare($query);
        foreach ($bindedParams as $bindedParam => $bindedValue) {
            $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Return ids of services linked to templates recursively
     *
     * @param int[] $serviceTemplateIds
     * @return int[]
     * @throws \Exception
     */
    private function findServicesForConfigChangeFlagFromServiceTemplateIds(array $serviceTemplateIds): array
    {
        if (empty($serviceTemplateIds)) {
            return [];
        }

        $bindedParams = [];
        foreach ($serviceTemplateIds as $key => $serviceTemplateId) {
            $bindedParams[':servicetemplate_id_' . $key] = $serviceTemplateId;
        }

        $query = "SELECT service_id, service_register FROM service
            WHERE service.service_activate = '1'
            AND service_template_model_stm_id IN (" . implode(', ', array_keys($bindedParams)) . ")";

        $stmt = $this->db->prepare($query);
        foreach ($bindedParams as $bindedParam => $bindedValue) {
            $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
        }
        $stmt->execute();

        $serviceIds = [];
        $serviceTemplateIds2 = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            if ($value['service_register'] === '0') {
                $serviceTemplateIds2[] = $value['service_id'];
            } else {
                $serviceIds[] = $value['service_id'];
            }
        }
        return array_merge(
            $serviceIds,
            $this->findServicesForConfigChangeFlagFromServiceTemplateIds($serviceTemplateIds2)
        );
    }

    /**
     * Return ids of hosts linked to service
     *
     * @param int $servicegroupId
     * @param bool $shouldServicegroupBeEnabled (default true)
     * @return int[]
     * @throws \Exception
     */
    public function findHostsForConfigChangeFlagFromServiceGroupId(
        int $servicegroupId,
        bool $shouldServicegroupBeEnabled = true
    ): array {
        $query = "SELECT sgr.*, service.service_register
            FROM servicegroup_relation sgr
            JOIN servicegroup ON servicegroup.sg_id = sgr.servicegroup_sg_id
            JOIN service ON service.service_id = sgr.service_service_id
            WHERE service.service_activate = '1' AND sgr.servicegroup_sg_id = :servicegroup_id"
            . ($shouldServicegroupBeEnabled ? " AND servicegroup.sg_activate = '1'" : "");

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':servicegroup_id', $servicegroupId, \PDO::PARAM_INT);
        $stmt->execute();

        $hostIds = [];
        $hostgroupIds = [];
        $serviceTemplateIds = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            if ($value['service_register'] === '0') {
                $serviceTemplateIds[] = $value['service_service_id'];
            } elseif ($value['hostgroup_hg_id'] !== null) {
                $hostgroupIds[] = $value['hostgroup_hg_id'];
            } else {
                $hostIds[] = $value['host_host_id'];
            }
        }

        $serviceIds = $this->findServicesForConfigChangeFlagFromServiceTemplateIds($serviceTemplateIds);

        return array_merge(
            $hostIds,
            $this->findHostsForConfigChangeFlagFromHostGroupIds($hostgroupIds),
            $this->findHostsForConfigChangeFlagFromServiceIds($serviceIds)
        );
    }

    /**
     * Return ids of pollers linked to hosts
     *
     * @param int[] $hostIds
     * @param bool $shouldHostBeEnabled (default true)
     * @return int[]
     * @throws \Exception
     */
    public function findPollersForConfigChangeFlagFromHostIds(array $hostIds, bool $shouldHostBeEnabled = true): array
    {
        if (empty($hostIds)) {
            return [];
        }

        $bindedParams = [];
        foreach ($hostIds as $key => $hostId) {
            $bindedParams[':host_id_' . $key] = $hostId;
        }

        if ($shouldHostBeEnabled) {
            $query = "SELECT DISTINCT(phr.nagios_server_id)
            FROM ns_host_relation phr
            JOIN host ON host.host_id = phr.host_host_id
            WHERE host.host_activate = '1' AND phr.host_host_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
        } else {
            $query = "SELECT DISTINCT(phr.nagios_server_id) FROM ns_host_relation phr
            WHERE phr.host_host_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
        }

        $stmt = $this->db->prepare($query);
        foreach ($bindedParams as $bindedParam => $bindedValue) {
            $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Set 'updated' flag to '1' for all listed poller ids
     *
     * @param int[] $pollerIds
     * @throws \Exception
     */
    private function definePollersToUpdated(array $pollerIds): void
    {
        if (empty($pollerIds)) {
            return;
        }

        $bindedParams = [];
        foreach ($pollerIds as $key => $pollerId) {
            $bindedParams[':poller_id_' . $key] = $pollerId;
        }
        $query = "UPDATE nagios_server SET updated = '1' WHERE id IN ("
            . implode(', ', array_keys($bindedParams)) . ")";
        $stmt = $this->db->prepare($query);
        foreach ($bindedParams as $bindedParam => $bindedValue) {
            $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    /**
     * Set relevent pollers as updated
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param int[] $previousPollers
     * @param bool $shouldResourceBeEnabled (default true)
     * @throws \Exception
     */
    public function signalConfigurationChange(
        string $resourceType,
        int $resourceId,
        array $previousPollers = [],
        bool $shouldResourceBeEnabled = true
    ): void {
        $hostIds = [];
        switch ($resourceType) {
            case self::RESOURCE_TYPE_HOST:
                $hostIds[] = $resourceId;
                break;
            case self::RESOURCE_TYPE_HOSTGROUP:
                $hostIds = array_merge(
                    $hostIds,
                    $this->findHostsForConfigChangeFlagFromHostGroupIds([$resourceId], $shouldResourceBeEnabled)
                );
                break;
            case self::RESOURCE_TYPE_SERVICE:
                $hostIds = array_merge(
                    $hostIds,
                    $this->findHostsForConfigChangeFlagFromServiceIds([$resourceId], $shouldResourceBeEnabled)
                );
                break;
            case self::RESOURCE_TYPE_SERVICEGROUP:
                $hostIds = array_merge(
                    $hostIds,
                    $this->findHostsForConfigChangeFlagFromServiceGroupId($resourceId, $shouldResourceBeEnabled)
                );
                break;
            default:
                throw new CentreonClapiException(self::UNKNOWN_RESOURCE_TYPE . ":" . $resourceType);
            break;
        }
        $pollerIds = $this->findPollersForConfigChangeFlagFromHostIds(
            $hostIds,
            $resourceType === self::RESOURCE_TYPE_HOST ? $shouldResourceBeEnabled : true
        );

        $this->definePollersToUpdated(array_merge($pollerIds, $previousPollers));
    }
}
