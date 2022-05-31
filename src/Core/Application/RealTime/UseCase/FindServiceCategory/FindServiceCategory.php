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

use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Configuration\Broker\BrokerBBDO;
use Core\Application\Common\UseCase\IncompatibilityResponse;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;

class FindServiceCategory extends BrokerBBDO
{
    use LoggerTrait;

    /**
     * @param ReadTagRepositoryInterface $repository
     * @param BrokerRepositoryInterface $brokerRepository
     */
    public function __construct(
        private ReadTagRepositoryInterface $repository,
        protected BrokerRepositoryInterface $brokerRepository
    ) {
        parent::__construct($brokerRepository);
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
                    $this->handleIncompatibleBBDOVersion($presenter);
                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->error('An error occured while retrieving service categories');
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
     */
    private function handleIncompatibleBBDOVersion(FindServiceCategoryPresenterInterface $presenter): void
    {
        $message = 'BBDO protocol version enabled not compatible with this feature. Version needed '
            . self::MINIMUM_BBDO_VERSION_SUPPORTED . ' or higher';
        $this->error($message);
        $presenter->setResponseStatus(new IncompatibilityResponse($message));
    }
}
