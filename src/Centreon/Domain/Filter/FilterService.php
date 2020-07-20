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

use Centreon\Domain\Filter\Interfaces\FilterRepositoryInterface;
use Centreon\Domain\Filter\Interfaces\FilterServiceInterface;

/**
 * This class is designed to manage monitoring servers and their associated resources.
 *
 * @package Centreon\Domain\Filter
 */
class FilterService implements FilterServiceInterface
{
    /**
     * @var FilterRepositoryInterface
     */
    private $filterRepository;

    /**
     * FilterService constructor.
     * @param FilterRepositoryInterface $filterRepository
     */
    public function __construct(FilterRepositoryInterface $filterRepository)
    {
        $this->filterRepository = $filterRepository;
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
