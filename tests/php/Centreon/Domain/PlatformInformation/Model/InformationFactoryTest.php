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

use Centreon\Domain\PlatformInformation\Model\InformationFactory;
use PHPUnit\Framework\TestCase;

class InformationFactoryTest extends TestCase
{
    /**
     * @var array<Information>
     */
    private $information;

    /**
     * @var array<string,mixed>
     */
    private $informationRequest;

    protected function setUp(): void
    {
        $this->information = InformationTest::createEntities();
        $this->informationRequest = [
            'isRemote' => true,
            'centralServerAddress' => '1.1.1.10',
            'apiUsername' => 'admin',
            'apiCredentials' => 'centreon',
            'apiScheme' => 'http',
            'apiPort' => 80,
            'apiPath' => 'centreon',
            'peerValidation' => false
        ];
    }

    /**
     * Test the Information instances are correctly created from a request body.
     */
    public function testCreateFromRequest(): void
    {
        $information = InformationFactory::createFromDto($this->informationRequest);
        $this->assertCount(count($information), $this->informationRequest);

        foreach ($information as $informationObject) {
            $this->assertEquals(
                $this->informationRequest[$informationObject->getKey()],
                $informationObject->getValue()
            );
        }
        $this->assertEquals($this->information, $information);
    }
}
