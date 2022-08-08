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

use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Media\Model\Image;
use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\HostConfiguration\Exception\HostTemplateArgumentException;

/**
 * This class is designed to test all setters of the HostTemplate entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostTemplateTest extends TestCase
{
    /**
     * Test the notification options
     */
    public function testBadNotificationOptionException(): void
    {
        $optionsValueToTest = (
            HostTemplate::NOTIFICATION_OPTION_DOWN
            | HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
            | HostTemplate::NOTIFICATION_OPTION_RECOVERY
            | HostTemplate::NOTIFICATION_OPTION_FLAPPING
            | HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED
        ) + 1;
        $this->expectException(HostTemplateArgumentException::class);
        $this->expectExceptionMessage(
            HostTemplateArgumentException::badNotificationOptions($optionsValueToTest)->getMessage()
        );
        $hostTemplate = new HostTemplate();
        $hostTemplate->setNotificationOptions($optionsValueToTest);
    }

    /**
     * Test the stalking options
     */
    public function testBadStalkingOptionException(): void
    {
        $optionsValueToTest = (
            HostTemplate::STALKING_OPTION_UP
            | HostTemplate::STALKING_OPTION_DOWN
            | HostTemplate::STALKING_OPTION_UNREACHABLE
        ) + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            HostTemplateArgumentException::badStalkingOptions($optionsValueToTest)->getMessage()
        );
        $hostTemplate = new HostTemplate();
        $hostTemplate->setStalkingOptions($optionsValueToTest);
    }

    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', HostTemplate::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                HostTemplate::MAX_NAME_LENGTH,
                'HostTemplate::name'
            )->getMessage()
        );
        (new HostTemplate())->setName($name);
    }

    /**
     * Too long alias test
     */
    public function testAliasTooLongException(): void
    {
        $alias = str_repeat('.', HostTemplate::MAX_ALIAS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                HostTemplate::MAX_ALIAS_LENGTH,
                'HostTemplate::alias'
            )->getMessage()
        );
        (new HostTemplate())->setAlias($alias);
    }

    /**
     * Too long display name test
     */
    public function testDisplayNameTooLongException(): void
    {
        $displayName = str_repeat('.', HostTemplate::MAX_DISPLAY_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $displayName,
                strlen($displayName),
                HostTemplate::MAX_DISPLAY_NAME_LENGTH,
                'HostTemplate::displayName'
            )->getMessage()
        );
        (new HostTemplate())->setDisplayName($displayName);
    }

    /**
     * Too long address test
     */
    public function testAddressTooLongException(): void
    {
        $address = str_repeat('.', HostTemplate::MAX_ADDRESS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $address,
                strlen($address),
                HostTemplate::MAX_ADDRESS_LENGTH,
                'HostTemplate::address'
            )->getMessage()
        );
        (new HostTemplate())->setAddress($address);
    }

    /**
     * Too long comments test
     */
    public function testCommentTooLongException(): void
    {
        $comments = str_repeat('.', HostTemplate::MAX_COMMENTS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $comments,
                strlen($comments),
                HostTemplate::MAX_COMMENTS_LENGTH,
                'HostTemplate::comment'
            )->getMessage()
        );
        (new HostTemplate())->setComment($comments);
    }

    /**
     * Test the bad active checks status
     */
    public function testBadActiveChecksException(): void
    {
        $badActiveChecksStatus = 128;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            HostTemplateArgumentException::badActiveChecksStatus($badActiveChecksStatus)->getMessage()
        );
        (new HostTemplate())->setActiveChecksStatus($badActiveChecksStatus);
    }

    /**
     * Test the bad passive checks status
     */
    public function testBadPassiveChecksException(): void
    {
        $badPassiveChecksStatus = 128;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            HostTemplateArgumentException::badPassiveChecksStatus($badPassiveChecksStatus)->getMessage()
        );
        (new HostTemplate())->setPassiveChecksStatus($badPassiveChecksStatus);
    }

    /**
     * Test the max check attemps
     */
    public function testBadMaxCheckAttempts(): void
    {
        $maxCheckAttempts = HostTemplate::MIN_CHECK_ATTEMPS - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::min(
                HostTemplate::MIN_CHECK_ATTEMPS - 1,
                HostTemplate::MIN_CHECK_ATTEMPS,
                'HostTemplate::maxCheckAttempts'
            )->getMessage()
        );
        (new HostTemplate())->setMaxCheckAttempts($maxCheckAttempts);
    }

    /**
     * Test the check interval
     */
    public function testBadCheckInterval(): void
    {
        $checkInterval = HostTemplate::MIN_CHECK_INTERVAL - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::min(
                HostTemplate::MIN_CHECK_INTERVAL - 1,
                HostTemplate::MIN_CHECK_INTERVAL,
                'HostTemplate::checkInterval'
            )->getMessage()
        );
        (new HostTemplate())->setCheckInterval($checkInterval);
    }

    /**
     * Test the retry check interval
     */
    public function testBadRetryCheckInterval(): void
    {
        $retryCheckInterval = HostTemplate::MIN_RETRY_CHECK_INTERVAL - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::min(
                HostTemplate::MIN_RETRY_CHECK_INTERVAL - 1,
                HostTemplate::MIN_RETRY_CHECK_INTERVAL,
                'HostTemplate::retryCheckInterval'
            )->getMessage()
        );
        (new HostTemplate())->setRetryCheckInterval($retryCheckInterval);
    }

    /**
     * Test the notification interval
     */
    public function testBadNotificationInterval(): void
    {
        $notificationInterval = HostTemplate::MIN_NOTIFICATION_INTERVAL - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::greaterOrEqualThan(
                HostTemplate::MIN_NOTIFICATION_INTERVAL - 1,
                HostTemplate::MIN_NOTIFICATION_INTERVAL,
                'HostTemplate::notificationInterval'
            )->getMessage()
        );
        (new HostTemplate())->setNotificationInterval($notificationInterval);
    }

    /**
     * Test the first notification delay
     */
    public function testBadFirstNotificationDelay(): void
    {
        $firstNotificationDelay = HostTemplate::MIN_FIRST_NOTIFICATION_DELAY - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::greaterOrEqualThan(
                HostTemplate::MIN_FIRST_NOTIFICATION_DELAY - 1,
                HostTemplate::MIN_FIRST_NOTIFICATION_DELAY,
                'HostTemplate::firstNotificationDelay'
            )->getMessage()
        );
        (new HostTemplate())->setFirstNotificationDelay($firstNotificationDelay);
    }

    /**
     * Test the recovery notification delay
     */
    public function testBadRecoveryNotificationDelay(): void
    {
        $recoveryNotificationDelay = HostTemplate::MIN_RECOVERY_NOTIFICATION_DELAY - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::greaterOrEqualThan(
                HostTemplate::MIN_RECOVERY_NOTIFICATION_DELAY - 1,
                HostTemplate::MIN_RECOVERY_NOTIFICATION_DELAY,
                'HostTemplate::recoveryNotificationDelay'
            )->getMessage()
        );
        (new HostTemplate())->setRecoveryNotificationDelay($recoveryNotificationDelay);
    }

    /**
     * Test the snmp community
     */
    public function testBadSnmpCommunity(): void
    {
        $snmpCommunity = str_repeat('.', HostTemplate::MAX_SNMP_COMMUNITY_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $snmpCommunity,
                strlen($snmpCommunity),
                HostTemplate::MAX_SNMP_COMMUNITY_LENGTH,
                'HostTemplate::snmpCommunity'
            )->getMessage()
        );
        (new HostTemplate())->setSnmpCommunity($snmpCommunity);
    }

    /**
     * Test the snmp version
     */
    public function testBadSnmpVersion(): void
    {
        $snmpVersion = '4';
        $hostTemplate = new HostTemplate();
        $hostTemplate->setSnmpVersion($snmpVersion);
        $this->assertNull($hostTemplate->getSnmpVersion());
    }

    /**
     * Test the alternative icon text
     */
    public function testBadAlternativeIconText(): void
    {
        $alternativeText = str_repeat('.', HostTemplate::MAX_ALTERNATIF_ICON_TEXT + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alternativeText,
                strlen($alternativeText),
                HostTemplate::MAX_ALTERNATIF_ICON_TEXT,
                'HostTemplate::alternativeIcon'
            )->getMessage()
        );
        (new HostTemplate())->setAlternativeIcon($alternativeText);
    }

    /**
     * Test the url notes
     */
    public function testBadUrlNotes(): void
    {
        $urlNotes = str_repeat('.', HostTemplate::MAX_URL_NOTES + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $urlNotes,
                strlen($urlNotes),
                HostTemplate::MAX_URL_NOTES,
                'HostTemplate::urlNotes'
            )->getMessage()
        );
        (new HostTemplate())->setUrlNotes($urlNotes);
    }

    /**
     * Test the action url
     */
    public function testBadActionUrl(): void
    {
        $actionUrl = str_repeat('.', HostTemplate::MAX_ACTION_URL + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $actionUrl,
                strlen($actionUrl),
                HostTemplate::MAX_ACTION_URL,
                'HostTemplate::actionUrl'
            )->getMessage()
        );
        (new HostTemplate())->setActionUrl($actionUrl);
    }

    /**
     * Test the notes
     */
    public function testBadNotes(): void
    {
        $notes = str_repeat('.', HostTemplate::MAX_NOTES + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $notes,
                strlen($notes),
                HostTemplate::MAX_NOTES,
                'HostTemplate::notes'
            )->getMessage()
        );
        (new HostTemplate())->setNotes($notes);
    }

    /**
     * Test the parentIds
     */
    public function testParentIds(): void
    {
        $hostTemplate = new HostTemplate();
        $hostTemplate->setParentIds(['a', 2, 'c', 7]);
        $this->assertCount(2, $hostTemplate->getParentIds());
        $this->assertEquals([2, 7], $hostTemplate->getParentIds());
    }

    /**
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
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
