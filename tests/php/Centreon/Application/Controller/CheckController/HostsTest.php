<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Application\Controller\CheckController;

use Centreon\Domain\Check\Check;
use Centreon\Domain\Contact\Contact;

final class HostsTest extends ResourcesTestCase
{
    protected const METHOD_UNDER_TEST = 'checkHosts';
    protected const REQUIRED_ROLE_FOR_ADMIN = Contact::ROLE_HOST_CHECK;

    /**
     * @test
     */
    public function checkHostsLoopsOverDeserializedElements(): void
    {
        $this->assertResourcesLoopsOverDeserializedElements('checkHost');
    }

    /**
     * @test
     */
    public function checkHostsValidatesChecks(): void
    {
        $check = new Check();
        $validator = $this->mockCheckValidator($check, Check::VALIDATION_GROUPS_HOST_CHECK, 1);
        $this->assertResourceCheckValidatesChecks($validator, [$check]);
    }
}
