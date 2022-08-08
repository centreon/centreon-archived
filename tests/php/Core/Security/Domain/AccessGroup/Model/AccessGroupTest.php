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

namespace Tests\Core\Security\Domain\AccessGroup\Model;

use Assert\InvalidArgumentException;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\Domain\AccessGroup\Model\AccessGroup;

it('should thrown an Exception when an access group name is empty', function () {
    new AccessGroup(1, '', 'alias');
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('AccessGroup::name')->getMessage());

it('should thrown an Exception when an access group alias is empty', function () {
    new AccessGroup(1, 'access_group', '');
})->throws(InvalidArgumentException::class, AssertionException::notEmpty('AccessGroup::alias')->getMessage());
