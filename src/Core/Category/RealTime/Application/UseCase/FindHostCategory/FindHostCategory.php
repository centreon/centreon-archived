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

namespace Core\Category\RealTime\Application\UseCase\FindHostCategory;

use Centreon\Domain\Log\LoggerTrait;
use Core\Tag\RealTime\Domain\Model\Tag;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Tag\RealTime\Application\Repository\ReadTagRepositoryInterface;

class FindHostCategory
{
    use LoggerTrait;

    /**
     * @param ReadTagRepositoryInterface $repository
     */
    public function __construct(
        private ReadTagRepositoryInterface $repository
    ) {
    }

    /**
     * @param FindHostCategoryPresenterInterface $presenter
     */
    public function __invoke(FindHostCategoryPresenterInterface $presenter): void
    {
        $this->info('Searching for host categories');

        try {
            $hostCategories = $this->repository->findAllByTypeId(Tag::HOST_CATEGORY_TYPE_ID);
        } catch (\Throwable $e) {
            $this->error('An error occurred while retrieving host categories');
            $presenter->setResponseStatus(new ErrorResponse('An error occurred while retrieving host categories'));
            return;
        }

        $presenter->present(
            $this->createResponse($hostCategories)
        );
    }

    /**
     * @param Tag[] $categories
     * @return FindHostCategoryResponse
     */
    private function createResponse(array $categories): FindHostCategoryResponse
    {
        return new FindHostCategoryResponse($categories);
    }
}
