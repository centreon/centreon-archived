<?php
declare(strict_types=1);

namespace Centreon\Domain\Service;

use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Entity\Host;
use Centreon\Domain\Entity\Service;
use Centreon\Domain\Pagination;
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
     * @var Contact
     */
    private $contact;

    /**
     * MonitoringService constructor.
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
     * @return Service[]|null
     * @throws \Exception
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
     * @return Host[]|null
     * @throws \Exception
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
     * @param Contact $contact
     * @return mixed|void
     */
    public function filterByContact(Contact $contact): MonitoringServiceInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @param Contact $contact
     * @param int $contactId
     * @return Host
     */
    public function findOneHost(int $contactId): ?Host
    {
        // TODO: Implement findOneHost() method.
    }

    /**
     * @param Contact $contact
     * @param int $serviceId
     * @return Service
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
