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

namespace Tests\Core\Application\RealTime\UseCase\FindHostCategory;

use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Broker\BrokerConfiguration;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\ImcompatibilityResponse;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;
use Core\Application\RealTime\UseCase\FindHostCategory\FindHostCategory;
use Tests\Core\Application\RealTime\UseCase\FindHostCategory\FindHostCategoryPresenterStub;

it('Find all host categories', function () {
    $category = new Tag(1, 'host-category-name', Tag::HOST_CATEGORY_TYPE_ID);
    $repository = $this->createMock(ReadTagRepositoryInterface::class);
    $brokerRepository = $this->createMock(BrokerRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('findAllByTypeId')
        ->willReturn([$category]);

    $useCase = new FindHostCategory($repository, $brokerRepository);

    $presenter = new FindHostCategoryPresenterStub();
    $useCase($presenter);

    expect($presenter->response->tags)->toHaveCount(1);
    expect($presenter->response->tags[0]['id'])->toBe($category->getId());
    expect($presenter->response->tags[0]['name'])->toBe($category->getName());
});

it('Find all service categories repository error', function () {
    $repository = $this->createMock(ReadTagRepositoryInterface::class);
    $brokerRepository = $this->createMock(BrokerRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('findAllByTypeId')
        ->willThrowException(new \Exception());

    $useCase = new FindHostCategory($repository, $brokerRepository);

    $presenter = new FindHostCategoryPresenterStub();
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'An error occured while retrieving host categories'
    );
});

it('Find all host categories repository bbdo version imcompatible', function () {
    $repository = $this->createMock(ReadTagRepositoryInterface::class);
    $brokerRepository = $this->createMock(BrokerRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('findAllByTypeId')
        ->willReturn([]);

    $brokerConfiguration = (new BrokerConfiguration())
        ->setConfigurationKey('bbdo_version')
        ->setConfigurationValue('2.0.0');

    $brokerRepository->expects($this->once())
        ->method('findAllByParameterName')
        ->with('bbdo_version')
        ->willReturn([$brokerConfiguration]);

    $useCase = new FindHostCategory($repository, $brokerRepository);

    $presenter = new FindHostCategoryPresenterStub();
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ImcompatibilityResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'BBDO protocol version enabled not compatible with this feature. Version needed 3.0.0 or higher'
    );
});
