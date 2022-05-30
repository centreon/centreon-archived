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

use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\ImcompatibilityResponse;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;
use Core\Infrastructure\RealTime\Api\FindHostCategory\FindHostCategoryPresenter;

class FindHostCategory
{
    use LoggerTrait;

    private const MINIMUM_BBDO_VERSION_SUPPORTED = '3.0.0',
                  BBDO_VERSION_CONFIG_KEY = 'bbdo_version';

    /**
     * @param ReadTagRepositoryInterface $repository
     */
    public function __construct(
        private ReadTagRepositoryInterface $repository,
        private BrokerRepositoryInterface $brokerRepository
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
            if (empty($hostCategories)) {
                if (! $this->isBBDOVersionCompatible()) {
                    $this->handleImcompatibleBBDOVersion($presenter);
                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->error(
                'An error occured while retrieving host categories',
                [
                    'trace' => $e->getTraceAsString()
                ]
            );
            $presenter->setResponseStatus(new ErrorResponse('An error occured while retrieving host categories'));
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

    /**
     * @param FindHostCategoryPresenterInterface $presenter
     * @return void
     */
    private function handleImcompatibleBBDOVersion(FindHostCategoryPresenterInterface $presenter): void
    {
        $message = 'BBDO protocol version enabled not compatible with this feature. Version needed '
            . self::MINIMUM_BBDO_VERSION_SUPPORTED . ' or higher';
        $this->error($message);
        $presenter->setResponseStatus(new ImcompatibilityResponse($message));
    }

    /**
     * Checks if at least on monitoring server has BBDO protocol in version 3.0.0
     *
     * @return boolean
     */
    private function isBBDOVersionCompatible(): bool
    {
        $brokerConfigurations = $this->brokerRepository->findAllByParameterName(self::BBDO_VERSION_CONFIG_KEY);
        foreach ($brokerConfigurations as $brokerConfiguration) {
            if (
                version_compare(
                    $brokerConfiguration->getConfigurationValue(),
                    self::MINIMUM_BBDO_VERSION_SUPPORTED
                ) > 0
            ) {
                return true;
            }
        }
        return false;
    }
}
