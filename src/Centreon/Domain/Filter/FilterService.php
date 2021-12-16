<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Filter;

use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Filter\Interfaces\FilterRepositoryInterface;
use Centreon\Domain\Filter\Interfaces\FilterServiceInterface;
use Centreon\Domain\Monitoring\HostGroup\Interfaces\HostGroupServiceInterface;
use Centreon\Domain\Monitoring\ServiceGroup\Interfaces\ServiceGroupServiceInterface;

/**
 * This class is designed to manage monitoring servers and their associated resources.
 *
 * @package Centreon\Domain\Filter
 */
class FilterService extends AbstractCentreonService implements FilterServiceInterface
{
    /**
     * @var HostGroupServiceInterface
     */
    private $hostGroupService;

    /**
     * @var ServiceGroupServiceInterface
     */
    private $serviceGroupService;

    /**
     * @var FilterRepositoryInterface
     */
    private $filterRepository;

    /**
     * FilterService constructor.
     *
     * @param HostGroupServiceInterface $hostGroupService
     * @param ServiceGroupServiceInterface $serviceGroupService
     * @param FilterRepositoryInterface $filterRepository
     */
    public function __construct(
        HostGroupServiceInterface $hostGroupService,
        ServiceGroupServiceInterface $serviceGroupService,
        FilterRepositoryInterface $filterRepository
    ) {
        $this->hostGroupService = $hostGroupService;
        $this->serviceGroupService = $serviceGroupService;
        $this->filterRepository = $filterRepository;
    }

    /**
     * {@inheritDoc}
     * @param Contact|null $contact
     * @return FilterServiceInterface
     */
    public function filterByContact($contact): FilterServiceInterface
    {
        parent::filterByContact($contact);
        $this->hostGroupService->filterByContact($contact);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFilter(Filter $filter): int
    {
        $foundFilter = $this->filterRepository->findFilterByUserIdAndName(
            $filter->getUserId(),
            $filter->getPageName(),
            $filter->getName()
        );
        if ($foundFilter !== null) {
            throw new FilterException(_('Filter already exists'));
        }

        try {
            return $this->filterRepository->addFilter($filter);
        } catch (\Exception $ex) {
            throw new FilterException(
                sprintf(_('Error when adding filter %s'), $filter->getName()),
                0,
                $ex
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function updateFilter(Filter $filter): void
    {
        $foundFilter = $this->filterRepository->findFilterByUserIdAndName(
            $filter->getUserId(),
            $filter->getPageName(),
            $filter->getName()
        );
        if ($foundFilter !== null && $filter->getId() !== $foundFilter->getId()) {
            throw new FilterException(_('Filter name already used'));
        }

        try {
            $this->checkCriterias($filter->getCriterias());

            $this->filterRepository->updateFilter($filter);
        } catch (\Exception $ex) {
            throw new FilterException(
                sprintf(_('Error when updating filter %s'), $filter->getName()),
                0,
                $ex
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function checkCriterias(array $criterias): void
    {
        foreach ($criterias as $criteria) {
            if ($criteria->getType() === 'multi_select' && is_array($criteria->getValue())) {
                switch ($criteria->getObjectType()) {
                    case 'host_groups':
                        $hostGroupNames = array_column($criteria->getValue(), 'name');
                        $hostGroups = $this->hostGroupService
                            ->filterByContact($this->contact)
                            ->findHostGroupsByNames($hostGroupNames);
                        $criteria->setValue(array_map(
                            function ($hostGroup) {
                                return [
                                    'id' => $hostGroup->getId(),
                                    'name' => $hostGroup->getName(),
                                ];
                            },
                            $hostGroups
                        ));
                        break;
                    case 'service_groups':
                        $serviceGroupNames = array_column($criteria->getValue(), 'name');
                        $serviceGroups = $this->serviceGroupService
                            ->filterByContact($this->contact)
                            ->findServiceGroupsByNames($serviceGroupNames);
                        $criteria->setValue(array_map(
                            function ($serviceGroup) {
                                return [
                                    'id' => $serviceGroup->getId(),
                                    'name' => $serviceGroup->getName(),
                                ];
                            },
                            $serviceGroups
                        ));
                        break;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteFilter(Filter $filter): void
    {
        try {
            $this->filterRepository->deleteFilter($filter);
        } catch (\Exception $ex) {
            throw new FilterException(
                sprintf(_('Error when deleting filter %s'), $filter->getName()),
                0,
                $ex
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findFiltersByUserId(int $userId, string $pageName): array
    {
        try {
            return $this->filterRepository->findFiltersByUserIdWithRequestParameters($userId, $pageName);
        } catch (\Exception $ex) {
            throw new FilterException(_('Error when searching filters'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findFilterByUserId(int $userId, string $pageName, int $filterId): ?Filter
    {
        try {
            return $this->filterRepository->findFilterByUserIdAndId($userId, $pageName, $filterId);
        } catch (\Exception $ex) {
            throw new FilterException(
                sprintf(_('Error when searching filter id %d'), $filterId),
                0,
                $ex
            );
        }
    }
}
