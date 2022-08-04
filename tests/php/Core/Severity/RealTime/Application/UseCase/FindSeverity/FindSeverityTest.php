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

namespace Tests\Core\Severity\RealTime\Application\UseCase\FindSeverity;

use Core\Domain\RealTime\Model\Icon;
use Core\Severity\RealTime\Domain\Model\Severity;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Severity\RealTime\Application\UseCase\FindSeverity\FindSeverity;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;
use Core\Severity\RealTime\Application\UseCase\FindSeverity\FindSeverityResponse;

beforeEach(function () {
    $this->repository = $this->createMock(ReadSeverityRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
});

it('should present an ErrorResponse when an exception is thrown', function () {
    $useCase = new FindSeverity($this->repository);
    $this->repository
        ->expects($this->once())
        ->method('findAllByTypeId')
        ->with(Severity::HOST_SEVERITY_TYPE_ID)
        ->willThrowException(new \Exception());

    $presenter = new FindSeverityPresenterStub($this->presenterFormatter);
    $useCase(Severity::HOST_SEVERITY_TYPE_ID, $presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class)
        ->and($presenter->getResponseStatus()?->getMessage())
            ->toBe('An error occured while retrieving severities');
});

it('should present a FindSeverityResponse', function () {
    $useCase = new FindSeverity($this->repository);

    $icon = (new Icon())
        ->setId(1)
        ->setName('icon-name')
        ->setUrl('ppm/icon-name.png');

    $severity = new Severity(1, 'name', 50, Severity::HOST_SEVERITY_TYPE_ID, $icon);

    $this->repository
        ->expects($this->once())
        ->method('findAllByTypeId')
        ->willReturn([$severity]);

    $presenter = new FindSeverityPresenterStub($this->presenterFormatter);
    $useCase(Severity::HOST_SEVERITY_TYPE_ID, $presenter);
    expect($presenter->response)
        ->toBeInstanceOf(FindSeverityResponse::class)
        ->and($presenter->response->severities[0])->toBe(
            [
                'id' => 1,
                'name' => 'name',
                'level' => 50,
                'type' => 'host',
                'icon' => [
                    'id' => 1,
                    'name' => 'icon-name',
                    'url' => 'ppm/icon-name.png'
                ]
            ]
        );
});
