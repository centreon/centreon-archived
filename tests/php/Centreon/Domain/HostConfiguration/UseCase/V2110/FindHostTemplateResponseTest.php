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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V2110;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostTemplate\FindHostTemplatesResponse;
use Tests\Centreon\Domain\HostConfiguration\Model\HostTemplateTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\v2110
 */
class FindHostTemplateResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindHostTemplatesResponse();
        $hostTemplates = $response->getHostTemplates();
        $this->assertCount(0, $hostTemplates);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $hostTemplate = HostTemplateTest::createEntity();
        $response = new FindHostTemplatesResponse();
        $response->setHostTemplates([$hostTemplate]);
        $hostTemplates = $response->getHostTemplates();
        $this->assertCount(1, $hostTemplates);
        $this->assertEquals($hostTemplate->getId(), $hostTemplates[0]['id']);
        $this->assertEquals($hostTemplate->getName(), $hostTemplates[0]['name']);
        $this->assertEquals($hostTemplate->getAlias(), $hostTemplates[0]['alias']);
        $this->assertEquals($hostTemplate->getDisplayName(), $hostTemplates[0]['display_name']);
        $this->assertEquals($hostTemplate->getAddress(), $hostTemplates[0]['address']);
        $this->assertEquals($hostTemplate->getComment(), $hostTemplates[0]['comment']);
        $this->assertEquals($hostTemplate->isActivated(), $hostTemplates[0]['is_activated']);
        $this->assertEquals($hostTemplate->isLocked(), $hostTemplates[0]['is_locked']);
        $this->assertEquals($hostTemplate->getActiveChecksStatus(), $hostTemplates[0]['active_checks_status']);
        $this->assertEquals($hostTemplate->getPassiveChecksStatus(), $hostTemplates[0]['passive_checks_status']);
        $this->assertEquals($hostTemplate->getMaxCheckAttempts(), $hostTemplates[0]['max_check_attemps']);
        $this->assertEquals($hostTemplate->getCheckInterval(), $hostTemplates[0]['check_interval']);
        $this->assertEquals($hostTemplate->getRetryCheckInterval(), $hostTemplates[0]['retry_check_interval']);
        $this->assertEquals($hostTemplate->getNotificationsStatus(), $hostTemplates[0]['notifications_status']);
        $this->assertEquals($hostTemplate->getNotificationInterval(), $hostTemplates[0]['notification_interval']);
        $this->assertEquals($hostTemplate->getFirstNotificationDelay(), $hostTemplates[0]['first_notification_delay']);
        $this->assertEquals(
            $hostTemplate->getRecoveryNotificationDelay(),
            $hostTemplates[0]['recovery_notification_delay']
        );
        $this->assertEquals($hostTemplate->getNotificationOptions(), $hostTemplates[0]['notification_options']);
        $this->assertEquals($hostTemplate->getStalkingOptions(), $hostTemplates[0]['stalking_options']);
        $this->assertEquals($hostTemplate->getSnmpCommunity(), $hostTemplates[0]['snmp_community']);
        $this->assertEquals($hostTemplate->getSnmpVersion(), $hostTemplates[0]['snmp_version']);
        if ($hostTemplate->getIcon() !== null) {
            $this->assertEquals([
                'id' => $hostTemplate->getIcon()->getId(),
                'name' => $hostTemplate->getIcon()->getName(),
                'path' => $hostTemplate->getIcon()->getPath(),
                'comment' => $hostTemplate->getIcon()->getComment()
            ], $hostTemplates[0]['icon']);
        }

        if ($hostTemplate->getStatusMapImage() !== null) {
            $this->assertEquals([
                'id' => $hostTemplate->getStatusMapImage()->getId(),
                'name' => $hostTemplate->getStatusMapImage()->getName(),
                'path' => $hostTemplate->getStatusMapImage()->getPath(),
                'comment' => $hostTemplate->getStatusMapImage()->getComment()
            ], $hostTemplates[0]['status_map_image']);
        }

        $this->assertEquals($hostTemplate->getAlternativeIcon(), $hostTemplates[0]['alternative_icon']);
        $this->assertEquals($hostTemplate->getUrlNotes(), $hostTemplates[0]['url_notes']);
        $this->assertEquals($hostTemplate->getActionUrl(), $hostTemplates[0]['action_url']);
        $this->assertEquals($hostTemplate->getUrlNotes(), $hostTemplates[0]['url_notes']);
        $this->assertEquals($hostTemplate->getParentIds(), $hostTemplates[0]['parent_ids']);
    }
}
