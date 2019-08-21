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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring
 */
class MonitoringService implements MonitoringServiceInterface
{
    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * The contact will be use to filter services and hosts.
     * @var Contact
     */
    private $contact;

    /**
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function findServices(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServices();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServices();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(int $hostId): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServicesByHost($hostId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServicesByHost($hostId);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHosts(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findHosts();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findHosts();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findHostGroups();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findHostGroups();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function filterByContact(ContactInterface $contact): MonitoringServiceInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findOneHost($hostId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findOneHost($hostId);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findOneService($hostId, $serviceId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findOneService($hostId, $serviceId);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroups(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServiceGroups();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServiceGroups();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isHostExists(int $hostId): bool
    {
        return !is_null($this->findOneHost($hostId));
    }
}
