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

namespace Tests\Core\Severity\RealTime\Domain\Model;

use Core\Domain\RealTime\Model\Icon;
use Core\Severity\RealTime\Domain\Model\Severity;
use Centreon\Domain\Common\Assertion\AssertionException;

beforeEach(function () {
    $this->icon = (new Icon())
        ->setName('icon-name')
        ->setUrl('ppm/icon-name.png');
});

it('should throw an exception when severity name is empty', function () {
    new Severity(1, '', 50, Severity::HOST_SEVERITY_TYPE_ID, $this->icon);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::notEmpty('Severity::name')
        ->getMessage()
);

it('should throw an exception when severity name is too long', function () {
    new Severity(1, str_repeat('a', Severity::MAX_NAME_LENGTH + 1), 50, Severity::HOST_SEVERITY_TYPE_ID, $this->icon);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::maxLength(
        str_repeat('a', Severity::MAX_NAME_LENGTH + 1),
        Severity::MAX_NAME_LENGTH + 1,
        Severity::MAX_NAME_LENGTH,
        'Severity::name'
    )->getMessage()
);

it('should throw an exception when severity level is lower than 0', function () {
    new Severity(1, 'name', -1, Severity::HOST_SEVERITY_TYPE_ID, $this->icon);
})->throws(\Assert\InvalidArgumentException::class, AssertionException::min(-1, 0, 'Severity::level')->getMessage());

it('should throw an exception when severity level is greater than 100', function () {
    new Severity(1, 'name', 200, Severity::HOST_SEVERITY_TYPE_ID, $this->icon);
})->throws(\Assert\InvalidArgumentException::class, AssertionException::max(200, 100, 'Severity::level')->getMessage());

it('should throw an exception when severity type is not handled', function () {
    new Severity(1, 'name', 60, 2, $this->icon);
})->throws(
    \Assert\InvalidArgumentException::class,
    AssertionException::inArray(
        2,
        [Severity::HOST_SEVERITY_TYPE_ID, Severity::SERVICE_SEVERITY_TYPE_ID],
        'Severity::type'
    )->getMessage()
);
