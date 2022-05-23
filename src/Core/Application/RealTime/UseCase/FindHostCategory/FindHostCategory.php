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

namespace Core\Application\RealTime\UseCase\FindHostCategory;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Domain\RealTime\Model\HostCategory;
use Core\Application\RealTime\Repository\ReadHostCategoryRepositoryInterface;

class FindHostCategory
{
    use LoggerTrait;

    /**
     * @param ReadHostCategoryRepositoryInterface $repository
     */
    public function __construct(private ReadHostCategoryRepositoryInterface $repository)
    {
    }

    /**
     * @param FindHostCategoryPresenterInterface $presenter
     */
    public function __invoke(FindHostCategoryPresenterInterface $presenter): void
    {
        $this->info('Searching for host categories');

        try {
            $categories = $this->repository->findAll();
        } catch (\Throwable $e) {
            $presenter->setResponseStatus(new ErrorResponse($e->getMessage()));
            return;
        }

        $presenter->present(
            $this->createResponse($categories)
        );
    }

    /**
     * @param \Traversable<int, HostCategory> $categories
     * @return FindHostCategoryResponse
     */
    private function createResponse(\Traversable $categories): FindHostCategoryResponse
    {
        return new FindHostCategoryResponse($categories);
    }
}
