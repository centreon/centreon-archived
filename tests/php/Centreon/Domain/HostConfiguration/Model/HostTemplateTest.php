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

namespace Tests\Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\Media\Model\Image;
use PHPUnit\Framework\TestCase;

/**
 * This class is designed to test certain methods of the HostTemplate entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostTemplateTest extends TestCase
{
    public function testBadNotificationOptionException(): void
    {
        $optionsValueToTest = 32;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid notification option (%d)', $optionsValueToTest));
        $hostTemplate = new HostTemplate();
        $hostTemplate->setNotificationOptions($optionsValueToTest);
    }

    public function testBadStalkingOptionException(): void
    {
        $optionsValueToTest = 8;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid stalking option (%d)', $optionsValueToTest));
        $hostTemplate = new HostTemplate();
        $hostTemplate->setStalkingOptions($optionsValueToTest);
    }

    public static function createEntity(): HostTemplate
    {
        return (new HostTemplate())
            ->setId(10)
            ->setName('OS-Linux-SNMP-custom')
            ->setAlias('Template to check Linux server using SNMP protocol')
            ->setActivated(true)
            ->setLocked(false)
            ->setActiveChecksStatus(HostTemplate::STATUS_DEFAULT)
            ->setPassiveChecksStatus(HostTemplate::STATUS_DEFAULT)
            ->setNotificationsStatus(HostTemplate::STATUS_DEFAULT)
            ->setNotificationOptions(
                HostTemplate::NOTIFICATION_OPTION_DOWN
                | HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
                | HostTemplate::NOTIFICATION_OPTION_RECOVERY
                | HostTemplate::NOTIFICATION_OPTION_FLAPPING
                | HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED
            )
            ->setIcon((new Image())->setId(1)->setName('my icon')->setPath('/'))
            ->setStatusMapImage((new Image())->setId(2)->setName('my status map image')->setPath('/'))
            ->setParentIds([9]);
    }
}
