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

namespace Tests\Core\Application\RealTime\UseCase\FindServiceCategory;

use Core\Domain\RealTime\Model\ServiceCategory;
use Core\Application\RealTime\UseCase\FindServiceCategory\FindServiceCategory;
use Core\Application\RealTime\Repository\ReadServiceCategoryRepositoryInterface;
use Tests\Core\Application\RealTime\UseCase\FindServiceCategory\FindServiceCategoryPresenterStub;

it('Find all service categories', function () {
    $category = new ServiceCategory(1, 'category-name');
    $repository = $this->createMock(ReadServiceCategoryRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('findAll')
        ->willReturn([$category]);

    $findServiceCategoryUseCase = new FindServiceCategory($repository);

    $findServiceCategoryPresenter = new FindServiceCategoryPresenterStub();
    $findServiceCategoryUseCase($findServiceCategoryPresenter);

    expect($findServiceCategoryPresenter->response->serviceCategories)->toHaveCount(1);
    expect($findServiceCategoryPresenter->response->serviceCategories[0]['id'])->toBe($category->getId());
    expect($findServiceCategoryPresenter->response->serviceCategories[0]['name'])->toBe($category->getName());
});
