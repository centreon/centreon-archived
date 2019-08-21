<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Monitoring;

use JMS\Serializer\Annotation as Serializer;
use Centreon\Domain\Annotation\EntityDescriptor as Desc;

/**
 * Class representing a record of a service group in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class ServiceGroup
{
    /**
     * @Serializer\Groups({"sg_main"})
     * @Desc(column="servicegroup_id", modifier="setId")
     * @var int
     */
    private $id;

    /**
     * @Serializer\Groups({"sg_main"})
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @Serializer\Groups({"sg_main"})
     * @var string|null
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ServiceGroup
     */
    public function setId(int $id): ServiceGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Host $host
     * @return ServiceGroup
     */
    public function addHost(Host $host):ServiceGroup
    {
        $this->hosts[] = $host;
        return $this;
    }


    /**
     * @return Host[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * @param Host[] $hosts
     * @return ServiceGroup
     */
    public function setHosts(array $hosts): ServiceGroup
    {
        $this->hosts = $hosts;
        return $this;
    }

    /**
     * Indicates if a host exists in this service group.
     *
     * @param int $hostId Host id to find
     * @return bool
     */
    public function isHostExists(int $hostId): bool
    {
        foreach ($this->hosts as $host) {
            if ($host->getId() === $hostId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ServiceGroup
     */
    public function setName(?string $name): ServiceGroup
    {
        $this->name = $name;
        return $this;
    }
}
