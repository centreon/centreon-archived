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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroupsResponse;
use Tests\Centreon\Domain\HostConfiguration\Model\HostGroupTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostGroup
 */
class FindHostGroupsResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindHostGroupsResponse();
        $hostGroups = $response->getHostGroups();
        $this->assertCount(0, $hostGroups);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $hostGroup = HostGroupTest::createEntity();
        $response = new FindHostGroupsResponse();
        $response->setHostGroups([$hostGroup]);
        $hostGroups = $response->getHostGroups();
        $this->assertCount(1, $hostGroups);
        $this->assertEquals($hostGroup->getId(), $hostGroups[0]['id']);
        $this->assertEquals($hostGroup->getName(), $hostGroups[0]['name']);
        $this->assertEquals($hostGroup->getAlias(), $hostGroups[0]['alias']);
        if ($hostGroup->getIcon() !== null) {
            $this->assertEquals(
                [
                    'id' => $hostGroup->getIcon()->getId(),
                    'name' => $hostGroup->getIcon()->getName(),
                    'path' => $hostGroup->getIcon()->getPath(),
                    'comment' => $hostGroup->getIcon()->getComment()
                ],
                $hostGroups[0]['icon']
            );
        }
        if ($hostGroup->getIconMap() !== null) {
            $this->assertEquals(
                [
                    'id' => $hostGroup->getIconMap()->getId(),
                    'name' => $hostGroup->getIconMap()->getName(),
                    'path' => $hostGroup->getIconMap()->getPath(),
                    'comment' => $hostGroup->getIconMap()->getComment()
                ],
                $hostGroups[0]['icon_map']
            );
        }
        $this->assertEquals($hostGroup->getRrd(), $hostGroups[0]['rrd']);
        $this->assertEquals($hostGroup->getNotes(), $hostGroups[0]['notes']);
        $this->assertEquals($hostGroup->getActionUrl(), $hostGroups[0]['action_url']);
        $this->assertEquals($hostGroup->getGeoCoords(), $hostGroups[0]['geo_coords']);
        $this->assertEquals($hostGroup->getComment(), $hostGroups[0]['comment']);
        $this->assertEquals($hostGroup->isActivated(), $hostGroups[0]['is_activated']);
    }
}
