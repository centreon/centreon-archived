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

namespace Tests\Centreon\Infrastructure\HostConfiguration\API\Model;

use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\HostConfiguration\UseCase\V21\HostTemplate\FindHostTemplatesResponse;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostTemplateV21Factory;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostTemplateTest;

/**
 * @package Tests\Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostTemplateV21FactoryTest extends TestCase
{
    /**
     * @var HostTemplate
     */
    private $hostTemplate;

    protected function setUp(): void
    {
        $this->hostTemplate = HostTemplateTest::createEntity();
    }

    /**
     * We check the format sent for the API request (v2.1) using the factory
     */
    public function testCreateFromResponse(): void
    {
        $response = new FindHostTemplatesResponse();
        $response->setHostTemplates([$this->hostTemplate]);
        $hostTemplateV21 = HostTemplateV21Factory::createFromResponse($response);

        $oneHostTemplates = $response->getHostTemplates()[0];
        $this->assertCount(count($response->getHostTemplates()), $response->getHostTemplates());

        $this->assertEquals($oneHostTemplates['id'], $hostTemplateV21[0]->id);
        $this->assertEquals($oneHostTemplates['name'], $hostTemplateV21[0]->name);
        $this->assertEquals($oneHostTemplates['alias'], $hostTemplateV21[0]->alias);
        $this->assertEquals($oneHostTemplates['display_name'], $hostTemplateV21[0]->displayName);
        $this->assertEquals($oneHostTemplates['address'], $hostTemplateV21[0]->address);
        $this->assertEquals($oneHostTemplates['comment'], $hostTemplateV21[0]->comment);
        $this->assertEquals($oneHostTemplates['is_activated'], $hostTemplateV21[0]->isActivated);
        $this->assertEquals($oneHostTemplates['is_locked'], $hostTemplateV21[0]->isLocked);
        $this->assertEquals($oneHostTemplates['active_checks_status'], $hostTemplateV21[0]->activeChecksStatus);
        $this->assertEquals($oneHostTemplates['passive_checks_status'], $hostTemplateV21[0]->passiveChecksStatus);
        $this->assertEquals($oneHostTemplates['max_check_attemps'], $hostTemplateV21[0]->maxCheckAttemps);
        $this->assertEquals($oneHostTemplates['check_interval'], $hostTemplateV21[0]->checkInterval);
        $this->assertEquals($oneHostTemplates['retry_check_interval'], $hostTemplateV21[0]->retryCheckInterval);
        $this->assertEquals($oneHostTemplates['notifications_status'], $hostTemplateV21[0]->notificationsStatus);
        $this->assertEquals($oneHostTemplates['notification_interval'], $hostTemplateV21[0]->notificationInterval);
        $this->assertEquals($oneHostTemplates['first_notification_delay'], $hostTemplateV21[0]->firstNotificationDelay);
        $this->assertEquals(
            $oneHostTemplates['recovery_notification_delay'],
            $hostTemplateV21[0]->recoveryNotificationDelay
        );
        $this->assertEquals($oneHostTemplates['notification_options'], $hostTemplateV21[0]->notificationOptions);
        $this->assertEquals($oneHostTemplates['stalking_options'], $hostTemplateV21[0]->stalkingOptions);
        $this->assertEquals($oneHostTemplates['snmp_community'], $hostTemplateV21[0]->snmpCommunity);
        $this->assertEquals($oneHostTemplates['snmp_version'], $hostTemplateV21[0]->snmpVersion);
        $this->assertEquals($oneHostTemplates['icon'], $hostTemplateV21[0]->icon);
        $this->assertEquals($oneHostTemplates['alternative_icon'], $hostTemplateV21[0]->alternativeIcon);
        $this->assertEquals($oneHostTemplates['status_map_image'], $hostTemplateV21[0]->statusMapImage);
        $this->assertEquals($oneHostTemplates['url_notes'], $hostTemplateV21[0]->urlNotes);
        $this->assertEquals($oneHostTemplates['action_url'], $hostTemplateV21[0]->actionUrl);
        $this->assertEquals($oneHostTemplates['notes'], $hostTemplateV21[0]->notes);
        $this->assertEquals($oneHostTemplates['parent_ids'], $hostTemplateV21[0]->parentIds);
    }
}
