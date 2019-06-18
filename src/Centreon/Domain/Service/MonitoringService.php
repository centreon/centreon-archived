<?php
declare(strict_types=1);

namespace Centreon\Domain\Service;

use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Interfaces\ContactInterface;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination\Pagination;
use Centreon\Domain\Repository\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Repository\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Service\Interfaces\MonitoringServiceInterface;

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
    public function findServices(Pagination $pagination): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServices($pagination);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServices($pagination);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHosts(Pagination $pagination): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findHosts($pagination);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findHosts($pagination);
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
        // TODO: Implement findOneHost() method.
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $serviceId): ?Service
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findOneService($serviceId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findOneService($serviceId);
        }
        return null;
    }
}
