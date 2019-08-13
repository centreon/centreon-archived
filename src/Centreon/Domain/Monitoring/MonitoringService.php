<?php
declare(strict_types=1);

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;

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
