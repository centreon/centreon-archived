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

namespace Tests\Centreon\Infrastructure\HostConfiguration\Model;

use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\HostConfiguration\Exception\HostTemplateFactoryException;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostTemplateFactoryRdb;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Infrastructure\HostConfiguration\Model
 */
class HostTemplateFactoryRdbTest extends TestCase
{
    /**
     * @var array<string, string|int> $rdbData
     */
    private $rdbData;

    private const ALL_NOTIFICATION_OPTIONS =
        HostTemplate::NOTIFICATION_OPTION_DOWN
        | HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
        | HostTemplate::NOTIFICATION_OPTION_RECOVERY
        | HostTemplate::NOTIFICATION_OPTION_FLAPPING
        | HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED;

    private const ALL_STALKING_OPTIONS =
        HostTemplate::STALKING_OPTION_UP
        | HostTemplate::STALKING_OPTION_DOWN
        | HostTemplate::STALKING_OPTION_UNREACHABLE;

    protected function setUp(): void
    {
        $this->rdbData = [
            'host_id' => 10,
            'host_name' => 'Template name',
            'host_alias' => 'Template alias',
            'host_address' => 'address',
            'display_name' => 'Template display name',
            'host_max_check_attempts' => 1,
            'host_check_interval' => 2,
            'host_retry_check_interval' => 1,
            'host_active_checks_enabled' => 2,
            'host_passive_checks_enabled' => 1,
            'host_notification_interval' => 1,
            'host_recovery_notification_delay' => 1,
            'host_notification_options' => 'd,u,r,f,s',
            'host_notifications_enabled' => 2,
            'host_first_notification_delay' => 1,
            'host_stalking_options' => 'o,d,u',
            'host_snmp_community' => 'community',
            'host_snmp_version' => '1',
            'host_comment' => 'comment',
            'host_locked' => 0,
            'host_activate' => 1,
            'ehi_notes' => 'notes',
            'ehi_notes_url' => 'notes url',
            'ehi_action_url' => 'action url',
            'parents' => '5,6'
        ];
    }

    /**
     * Tests the of the good creation of the HostTemplate entity.<br>
     * We test all properties.
     *
     * @throws HostTemplateFactoryException
     */
    public function testAllPropertiesOnCreate(): void
    {
        $hostTemplate = HostTemplateFactoryRdb::create($this->rdbData);
        $this->assertEquals($this->rdbData['host_id'], $hostTemplate->getId());
        $this->assertEquals($this->rdbData['host_name'], $hostTemplate->getName());
        $this->assertEquals($this->rdbData['host_alias'], $hostTemplate->getAlias());
        $this->assertEquals($this->rdbData['host_address'], $hostTemplate->getAddress());
        $this->assertEquals($this->rdbData['display_name'], $hostTemplate->getDisplayName());
        $this->assertEquals($this->rdbData['host_max_check_attempts'], $hostTemplate->getMaxCheckAttempts());
        $this->assertEquals($this->rdbData['host_check_interval'], $hostTemplate->getCheckInterval());
        $this->assertEquals($this->rdbData['host_retry_check_interval'], $hostTemplate->getRetryCheckInterval());
        $this->assertEquals(
            $this->rdbData['host_active_checks_enabled'],
            $hostTemplate->getActiveChecksStatus() === HostTemplate::STATUS_DEFAULT
        );
        $this->assertEquals(
            $this->rdbData['host_passive_checks_enabled'],
            $hostTemplate->getPassiveChecksStatus() === HostTemplate::STATUS_ENABLE
        );
        $this->assertEquals($this->rdbData['host_notification_interval'], $hostTemplate->getNotificationInterval());
        $this->assertEquals(
            $this->rdbData['host_recovery_notification_delay'],
            $hostTemplate->getRecoveryNotificationDelay()
        );
        $this->assertEquals(
            self::ALL_NOTIFICATION_OPTIONS,
            $hostTemplate->getNotificationOptions(),
            'The notification options of the HostTemplate are not set correctly.'
        );
        $this->assertEquals(
            $this->rdbData['host_notifications_enabled'],
            $hostTemplate->getNotificationsStatus() === (HostTemplate::STATUS_DEFAULT)
        );
        $this->assertEquals(
            $this->rdbData['host_first_notification_delay'],
            $hostTemplate->getFirstNotificationDelay()
        );
        $this->assertEquals(
            self::ALL_STALKING_OPTIONS,
            $hostTemplate->getStalkingOptions(),
            'The stalking options of the HostTemplate are not set correctly.'
        );
        $this->assertEquals($this->rdbData['host_snmp_community'], $hostTemplate->getSnmpCommunity());
        $this->assertEquals($this->rdbData['host_snmp_version'], $hostTemplate->getSnmpVersion());
        $this->assertEquals((bool) $this->rdbData['host_locked'], $hostTemplate->isLocked());
        $this->assertEquals((bool) $this->rdbData['host_activate'], $hostTemplate->isActivated());
        $this->assertEquals($this->rdbData['ehi_notes'], $hostTemplate->getNotes());
        $this->assertEquals($this->rdbData['ehi_notes_url'], $hostTemplate->getUrlNotes());
        $this->assertEquals($this->rdbData['ehi_action_url'], $hostTemplate->getActionUrl());
        $this->assertEquals($this->rdbData['parents'], implode(',', $hostTemplate->getParentIds()));
    }

    /**
     * We are testing a bad notification option.
     *
     * @throws HostTemplateFactoryException
     */
    public function testBadNotificationOptions(): void
    {
        $this->rdbData['host_notification_options'] = 'd,u,c,r,f,s,x';
        $this->expectException(HostTemplateFactoryException::class);
        $this->expectExceptionMessage(HostTemplateFactoryException::notificationOptionsNotAllowed('c,x')->getMessage());
        HostTemplateFactoryRdb::create($this->rdbData);
    }

    /**
     * We are testing a bad stalking option.
     *
     * @throws HostTemplateFactoryException
     */
    public function testBadStalkingOptions(): void
    {
        $this->rdbData['host_stalking_options'] = 'o,c,d,u,x';
        $this->expectException(HostTemplateFactoryException::class);
        $this->expectExceptionMessage(HostTemplateFactoryException::stalkingOptionsNotAllowed('c,x')->getMessage());
        HostTemplateFactoryRdb::create($this->rdbData);
    }
}
