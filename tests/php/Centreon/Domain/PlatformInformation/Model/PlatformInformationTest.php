<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Domain\PlatformInformation\Model;

use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use PHPUnit\Framework\TestCase;

class PlatformInformationTest extends TestCase
{
    /**
     * Invalid Information apiPort Test.
     *
     * @return void
     */
    public function testInvalidApiPortException(): void
    {
        $port = 0;
        $this->expectException(PlatformInformationException::class);
        $this->expectExceptionMessage(
            "Central platform's API data is not consistent. Please check the 'Remote Access' form."
        );
        (new PlatformInformation(true))->setApiPort($port);
    }

    /**
     * Invalid Information apiPath Test.
     *
     * @return void
     */
    public function testInvalidApiPathException(): void
    {
        $path = "";
        $this->expectException(PlatformInformationException::class);
        $this->expectExceptionMessage(
            "Central platform's API data is not consistent. Please check the 'Remote Access' form."
        );
        (new PlatformInformation(true))->setApiPath($path);
    }

    /**
     * Create Platform Information for a Central.
     *
     * @return PlatformInformation
     */
    public static function createEntityForCentralInformation(): PlatformInformation
    {
        return (new PlatformInformation(false));
    }

    /**
     * Create Platform Information for a Remote.
     *
     * @return PlatformInformation
     */
    public static function createEntityForRemoteInformation(): PlatformInformation
    {
        return (new PlatformInformation(true))
            ->setCentralServerAddress('1.1.1.10')
            ->setApiUsername('admin')
            ->setApiCredentials('centreon')
            ->setApiScheme('http')
            ->setApiPort(80)
            ->setApiPath('centreon')
            ->setApiPeerValidation(false);
    }
}
