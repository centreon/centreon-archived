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

namespace Tests\Core\Severity\RealTime\Application\UseCase;

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
        ->method('findAll')
        ->willThrowException(new \Exception());

    $presenter = new FindSeverityPresenterStub($this->presenterFormatter);
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'An error occured while retrieving severities'
    );
});

it('should present a FindSeverityResponse', function () {
    $useCase = new FindSeverity($this->repository);

    $icon = (new Icon())
        ->setName('icon-name')
        ->setUrl('ppm/icon-name.png');

    $severity = new Severity(1, 'name', 50, $icon);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn([$severity]);

    $presenter = new FindSeverityPresenterStub($this->presenterFormatter);
    $useCase($presenter);
    expect($presenter->response)
        ->toBeInstanceOf(FindSeverityResponse::class)
        ->and($presenter->response->severities[0])->toBe(
            [
                'id' => 1,
                'name' => 'name',
                'level' => 50,
                'icon' => [
                    'name' => 'icon-name',
                    'url' => 'ppm/icon-name.png'
                ]
            ]
        );
    expect($presenter->response->severities[0])->toBe(
        [
            'id' => 1,
            'name' => 'name',
            'level' => 50,
            'icon' => [
                'name' => 'icon-name',
                'url' => 'ppm/icon-name.png'
            ]
        ]
    );
});
