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
     * @var FilterRepositoryInterface
     */
    private $filterRepository;

    /**
     * FilterService constructor.
     *
     * @param HostGroupServiceInterface $hostGroupService
     * @param FilterRepositoryInterface $filterRepository
     */
    public function __construct(
        HostGroupServiceInterface $hostGroupService,
        FilterRepositoryInterface $filterRepository
    ) {
        $this->hostGroupService = $hostGroupService;
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
                sprintf(_('Error when adding filter %s', $filter->getName())),
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
        try {
            $criterias = [];
            foreach ($filter->getCriterias() as $criteria) {
                if (isset($criteria['object_type']) && isset($criteria['value'])) {
                    switch ($criteria['object_type']) {
                        case 'host_group':
                            $hostGroupIds = [];
                            foreach ($criteria['value'] as $value) {
                                $hostGroupIds[] = $value['id'];
                            }
                            $hostGroups = $this->hostGroupService
                                ->filterByContact($this->contact)
                                ->findHostGroupsByIds($hostGroupIds);
                            $values = [];
                            foreach ($hostGroups as $hostGroup) {
                                $values[] = [
                                    'id' => $hostGroup->getId(),
                                    'name' => $hostGroup->getName(),
                                ];
                            }
                            $criteria['value'] = $values;
                            break;
                    }
                    $criterias[] = $criteria;
                }
            }
            $filter->setCriterias($criterias);

            $this->filterRepository->updateFilter($filter);
        } catch (\Exception $ex) {
            throw new FilterException(
                sprintf(_('Error when updating filter %s', $filter->getName())),
                0,
                $ex
            );
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
                sprintf(_('Error when deleting filter %s', $filter->getName())),
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
                sprintf(_('Error when searching filter id %d', $filterId)),
                0,
                $ex
            );
        }
    }
}
