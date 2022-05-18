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

namespace Core\Infrastructure\RealTime\Api\FindServiceCategory;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\RealTime\UseCase\FindServiceCategory\FindServiceCategoryResponse;
use Core\Application\RealTime\UseCase\FindServiceCategory\FindServiceCategoryPresenterInterface;

class FindServiceCategoryPresenter extends AbstractPresenter implements FindServiceCategoryPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindServiceCategoryResponse $data
     */
    public function present(mixed $data): void
    {
        $presenterResponse = $data->serviceCategories;
        parent::present($presenterResponse);
    }
}
