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

namespace Tests\Centreon\Infrastructure\HostConfiguration\API\Model;

use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroupsResponse;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup\HostGroupV2110Factory;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostGroupTest;

/**
 * @package Tests\Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostGroupV2110FactoryTest extends TestCase
{
    /**
     * @var HostGroup
     */
    private $hostGroup;

    protected function setUp(): void
    {
        $this->hostGroup = HostGroupTest::createEntity();
    }

    /**
     * We check the format sent for the API request (v21.10) using the factory
     */
    public function testCreateFromResponse(): void
    {
        $response = new FindHostGroupsResponse();
        $response->setHostGroups([$this->hostGroup]);
        $hostGroupV21 = HostGroupV2110Factory::createFromResponse($response);

        $oneHostGroups = $response->getHostGroups()[0];
        $this->assertCount(count($response->getHostGroups()), $response->getHostGroups());
        $this->assertEquals($oneHostGroups['id'], $hostGroupV21[0]->id);
        $this->assertEquals($oneHostGroups['name'], $hostGroupV21[0]->name);
        $this->assertEquals($oneHostGroups['alias'], $hostGroupV21[0]->alias);
        $this->assertEquals($oneHostGroups['notes_url'], $hostGroupV21[0]->notesUrl);
        $this->assertEquals($oneHostGroups['action_url'], $hostGroupV21[0]->actionUrl);
        $this->assertEquals($oneHostGroups['notes'], $hostGroupV21[0]->notes);
        $this->assertEquals($oneHostGroups['comment'], $hostGroupV21[0]->comment);
        $this->assertEquals($oneHostGroups['icon'], $hostGroupV21[0]->icon);
        $this->assertEquals($oneHostGroups['icon_map'], $hostGroupV21[0]->iconMap);
        $this->assertEquals($oneHostGroups['is_activated'], $hostGroupV21[0]->isActivated);
    }
}
