<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Application\RealTime\UseCase\FindServiceCategory;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Domain\RealTime\Model\ServiceCategory;
use Core\Application\RealTime\Repository\ReadServiceCategoryRepositoryInterface;

class FindServiceCategory
{
    use LoggerTrait;

    /**
     * @param ReadServiceCategoryRepositoryInterface $repository
     */
    public function __construct(
        private ReadServiceCategoryRepositoryInterface $repository
    ) {
    }

    /**
     * @param FindServiceCategoryPresenterInterface $presenter
     */
    public function __invoke(FindServiceCategoryPresenterInterface $presenter): void
    {
        $this->info('Searching for service categories');

        try {
            $serviceCategories = $this->repository->findAll();
        } catch (\Throwable $e) {
            $presenter->setResponseStatus(new ErrorResponse($e->getMessage()));
            return;
        }

        $presenter->present(
            $this->createResponse($serviceCategories)
        );
    }

    /**
     * @param \Traversable<int, ServiceCategory> $serviceCategories
     * @return FindServiceCategoryResponse
     */
    private function createResponse(\Traversable $serviceCategories): FindServiceCategoryResponse
    {
        return new FindServiceCategoryResponse($serviceCategories);
    }
}
