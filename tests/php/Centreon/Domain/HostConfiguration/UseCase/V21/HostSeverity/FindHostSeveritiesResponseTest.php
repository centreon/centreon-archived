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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity;

use Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity\FindHostSeveritiesResponse;
use PHPStan\Testing\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostSeverityTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostSeverity
 */
class FindHostSeveritiesResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindHostSeveritiesResponse();
        $hostSeverities = $response->getHostSeverities();
        $this->assertCount(0, $hostSeverities);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $hostSeverity = HostSeverityTest::createEntity();
        $response = new FindHostSeveritiesResponse();
        $response->setHostSeverities([$hostSeverity]);
        $hostSeverities = $response->getHostSeverities();
        $this->assertCount(1, $hostSeverities);
        $this->assertEquals($hostSeverity->getId(), $hostSeverities[0]['id']);
        $this->assertEquals($hostSeverity->getName(), $hostSeverities[0]['name']);
        $this->assertEquals($hostSeverity->getAlias(), $hostSeverities[0]['alias']);
        $this->assertEquals(
            [
                'id' => $hostSeverity->getIcon()->getId(),
                'name' => $hostSeverity->getIcon()->getName(),
                'path' => $hostSeverity->getIcon()->getPath(),
                'comment' => $hostSeverity->getIcon()->getComment()
            ],
            $hostSeverities[0]['icon']
        );
        $this->assertEquals($hostSeverity->getLevel(), $hostSeverities[0]['level']);
        $this->assertEquals($hostSeverity->getComments(), $hostSeverities[0]['comments']);
        $this->assertEquals($hostSeverity->isActivated(), $hostSeverities[0]['is_activated']);
    }
}
