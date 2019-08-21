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

namespace Centreon\Domain\Monitoring\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;

interface MonitoringServiceInterface
{
    /**
     * Used to filter requests according to a contact.
     * If the filter is defined, all requests will use the ACL of the contact
     * to fetch data.
     *
     * @param ContactInterface $contact Contact to use as a ACL filter
     * @return self
     */
    public function filterByContact(ContactInterface $contact): MonitoringServiceInterface;

    /**
     * Find all hosts.
     *
     * @return Host[]
     * @throws \Exception
     */
    public function findHosts(): array;

    /**
     * Find all host groups.
     *
     * @return \HostGroup[]
     * @throws \Exception
     */
    public function findHostGroups(): array;

    /**
     * Find a host based on his ID.
     *
     * @param int $hostId Id of the host to be found
     * @return Host|null
     * @throws \Exception
     */
    public function findOneHost(int $hostId): ?Host;

    /**
     * Find a service based on his ID and a host ID.
     *
     * @param int $hostId Host ID for which the service belongs
     * @param int $serviceId Service ID to find
     * @return Service|null
     * @throws \Exception
     */
    public function findOneService(int $hostId, int $serviceId): ?Service;

    /**
     * Find all service groups.
     *
     * @return \Servicegroup[]
     * @throws \Exception
     */
    public function findServiceGroups(): array;

    /**
     * Find all services.
     *
     * @return Service[]
     * @throws \Exception
     */
    public function findServices(): array;

    /**
     * Find all services belonging to the host.
     *
     * @param int $hostId
     * @throws \Exception
     * @return Service[]
     */
    public function findServicesByHost(int $hostId): array;

    /**
     * Indicates whether a host exists.
     *
     * @param int $hostId Host id to find
     * @return bool
     * @throws \Exception
     */
    public function isHostExists(int $hostId): bool;
}
