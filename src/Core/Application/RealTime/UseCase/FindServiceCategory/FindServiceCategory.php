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

use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Broker\BrokerException;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\ImcompatibilityResponse;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;

class FindServiceCategory
{
    use LoggerTrait;

    private const MINIMUM_BBDO_VERSION_SUPPORTED = '3.0.0',
                  BBDO_VERSION_CONFIG_KEY = 'bbdo_version';

    /**
     * @param ReadTagRepositoryInterface $repository
     * @param BrokerRepositoryInterface $brokerRepository
     */
    public function __construct(
        private ReadTagRepositoryInterface $repository,
        private BrokerRepositoryInterface $brokerRepository
    ) {
    }

    /**
     * @param FindServiceCategoryPresenterInterface $presenter
     */
    public function __invoke(FindServiceCategoryPresenterInterface $presenter): void
    {
        $this->info('Searching for service categories');

        try {
            $serviceCategories = $this->repository->findAllByTypeId(Tag::SERVICE_CATEGORY_TYPE_ID);
            if (empty($serviceCategories)) {
                if (! $this->isBBDOVersionCompatible()) {
                    $this->handleImcompatibleBBDOVersion($presenter);
                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->error(
                'An error occured while retrieving service categories',
                [
                    'trace' => $e->getTraceAsString()
                ]
            );
            $presenter->setResponseStatus(new ErrorResponse('An error occured while retrieving service categories'));
            return;
        }

        $presenter->present(
            $this->createResponse($serviceCategories)
        );
    }

    /**
     * @param Tag[] $serviceCategories
     * @return FindServiceCategoryResponse
     */
    private function createResponse(array $serviceCategories): FindServiceCategoryResponse
    {
        return new FindServiceCategoryResponse($serviceCategories);
    }

    /**
     * @param FindServiceCategoryPresenterInterface $presenter
     * @return void
     */
    private function handleImcompatibleBBDOVersion(FindServiceCategoryPresenterInterface $presenter): void
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
