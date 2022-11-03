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

namespace Tests\Core\TimePeriod\Application\UseCase\FindTimePeriods;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\TimePeriod\Application\Repository\ReadTimePeriodRepositoryInterface;
use Core\TimePeriod\Application\UseCase\FindTimePeriods\FindTimePeriods;
use Core\TimePeriod\Application\UseCase\FindTimePeriods\FindTimePeriodsResponse;
use Core\TimePeriod\Domain\Model\TimePeriod;

use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    $this->repository = $this->createMock(ReadTimePeriodRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
});

it('should present an ErrorResponse when an exception is thrown', function () {
    $useCase = new FindTimePeriods($this->repository);
    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willThrowException(new \Exception());

    $presenter = new FindTimePeriodsPresenterStub($this->presenterFormatter);
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class)
        ->and($presenter->getResponseStatus()?->getMessage())
        ->toBe('Error while searching for the time periods');
});

it('should present a FindTimePeriodsResponse', function () {
    $useCase = new FindTimePeriods($this->repository);

    $timePeriod = new TimePeriod(1, 'fakeName', 'fakeAlias');

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn([$timePeriod]);

    $presenter = new FindTimePeriodsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
    assertCount(1, $presenter->response->timePeriods);
    expect($presenter->response)
        ->toBeInstanceOf(FindTimePeriodsResponse::class)
        ->and($presenter->response->timePeriods[0])->toBe(
            [
                'id' => 1,
                'name' => 'fakeName',
                'alias' => 'fakeAlias',
            ]
        );
});
