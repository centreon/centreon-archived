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

namespace Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\HostConfiguration\Exception\HostTemplateArgumentException;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to represent a host template.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostTemplate
{
    public const STATUS_ENABLE = 1,
                 STATUS_DISABLE = 0,
                 STATUS_DEFAULT = 2;

    public const NOTIFICATION_OPTION_DOWN = 1,
                 NOTIFICATION_OPTION_UNREACHABLE = 2,
                 NOTIFICATION_OPTION_RECOVERY = 4,
                 NOTIFICATION_OPTION_FLAPPING = 8,
                 NOTIFICATION_OPTION_DOWNTIME_SCHEDULED = 16;

    public const STALKING_OPTION_UP = 1,
                 STALKING_OPTION_DOWN = 2,
                 STALKING_OPTION_UNREACHABLE = 4;

    public const MAX_NAME_LENGTH = 200,
                 MAX_ALIAS_LENGTH = 200,
                 MAX_DISPLAY_NAME_LENGTH = 255,
                 MAX_ADDRESS_LENGTH = 255,
                 MAX_COMMENTS_LENGTH = 65535,
                 MAX_SNMP_COMMUNITY_LENGTH = 255,
                 MAX_ALTERNATIF_ICON_TEXT = 200,
                 MAX_URL_NOTES = 65535,
                 MAX_ACTION_URL = 65535,
                 MAX_NOTES = 65535,
                 MIN_CHECK_ATTEMPS = 1,
                 MIN_CHECK_INTERVAL = 1,
                 MIN_RETRY_CHECK_INTERVAL = 1,
                 MIN_NOTIFICATION_INTERVAL = 0,
                 MIN_FIRST_NOTIFICATION_DELAY = 0,
                 MIN_RECOVERY_NOTIFICATION_DELAY = 0;

    private const AVAILABLE_STATUS = [
        self::STATUS_ENABLE, self::STATUS_DISABLE, self::STATUS_DEFAULT
    ];

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null Display name
     */
    private $displayName;

    /**
     * @var string|null
     */
    private $address;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var int[] Host template parent ids
     */
    private $parentIds = [];

    /**
     * @var bool Indicates whether the host template is activated or not.
     */
    private $isActivated = true;

    /**
     * @var bool Indicates whether the configuration is locked for editing or not.
     */
    private $isLocked = false;

    /**
     * @var int Enable or disable active checks. By default active host checks are enabled.
     */
    private $activeChecksStatus = self::STATUS_DEFAULT;

    /**
     * @var int Enable or disable passive checks here. When disabled submitted states will be not accepted.
     */
    private $passiveChecksStatus = self::STATUS_DEFAULT;

    /**
     * @var int|null Number of checks before considering verified state (HARD).<br>
     * Define the number of times that monitoring engine will retry the host check command if it returns any non-OK
     * state.<br>
     * Setting this value to 1 will cause monitoring engine to generate an alert immediately.<br>
     * <b>Note: If you do not want to check the status of the host, you must still set this to a minimum value of 1.
     * <br>
     * To bypass the host check, just leave the check command option blank.</b>
     */
    private $maxCheckAttempts;

    /**
     * @var int|null Define the number of "time units" between regularly scheduled checks of the host.<br>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.
     */
    private $checkInterval;

    /**
     * @var int|null Define the number of "time units" to wait before scheduling a re-check for this host after a
     * non-UP state was detected.<br>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br>
     * Once the host has been retried max_check_attempts times without a change in its status,
     * it will revert to being scheduled at its "normal" check interval rate.
     */
    private $retryCheckInterval;

    /**
     * @var int Specify whether or not notifications for this host are enabled.
     */
    private $notificationsStatus = self::STATUS_DEFAULT;

    /**
     * @var int|null Define the number of "time units" to wait before re-notifying a contact that this host is still
     * down or unreachable.<br>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br>
     * A value of 0 disables re-notifications of contacts about problems for this host - only one problem notification
     * will be sent out.
     */
    private $notificationInterval;

    /**
     * @var int|null Define the number of "time units" to wait before sending out the first problem notification when
     * this host enters a non-UP state.<br>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br>
     * If you set this value to 0, monitoring engine will start sending out notifications immediately.
     */
    private $firstNotificationDelay;

    /**
     * @var int|null Define the number of "time units" to wait before sending out the recovery notification when this
     * host enters an UP state.<br>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br>
     * If you set this value to 0, monitoring engine will start sending out notifications immediately.
     */
    private $recoveryNotificationDelay;

    /**
     * @var int Define the states of the host for which notifications should be sent out.<br>
     * If you specify None as an option, no host notifications will be sent out.<br>
     * If you do not specify any notification options, monitoring engine will assume that you want notifications to be
     * sent out for all possible states.<br>
     * <b>Sets to 0 to define not options.</b>
     *
     * @see HostTemplate::NOTIFICATION_OPTION_DOWN
     * @see HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
     * @see HostTemplate::NOTIFICATION_OPTION_RECOVERY
     * @see HostTemplate::NOTIFICATION_OPTION_FLAPPING
     * @see HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED
     */
    private $notificationOptions = 0;

    /**
     * @var string|null Community of the SNMP agent.
     */
    private $snmpCommunity;

    /**
     * @var string|null Version of the SNMP agent.
     */
    private $snmpVersion;

    /**
     * @var Image|null Define the image that should be associated with this template.
     */
    private $icon;

    /**
     * @var string|null Define an optional string that is used in the alternative description of the icon image.
     */
    private $alternativeIcon;

    /**
     * @var Image|null Define an image that should be associated with this host template in the statusmap CGI in
     * monitoring engine.
     */
    private $statusMapImage;

    /**
     * @var string|null Define an optional URL that can be used to provide more information about the host.
     * <br>
     * This can be very useful if you want to make detailed information on the host template, emergency contact methods,
     * etc. available to other support staff.<br>
     * Any valid URL can be used.
     */
    private $urlNotes;

    /**
     * @var string|null Define an optional URL that can be used to provide more actions to be performed on the host.
     */
    private $actionUrl;

    /**
     * @var string|null Define an optional notes.
     */
    private $notes;

    /**
     * @var int This directive determines which host states "stalking" is enabled for.
     *
     * @see HostTemplate::STALKING_OPTION_UP
     * @see HostTemplate::STALKING_OPTION_DOWN
     * @see HostTemplate::STALKING_OPTION_UNREACHABLE
     */
    private $stalkingOptions = 0;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setName(?string $name): self
    {
        if ($name !== null) {
            Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'HostTemplate::name');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setAlias(?string $alias): self
    {
        if ($alias !== null) {
            Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'HostTemplate::alias');
        }
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setDisplayName(?string $displayName): self
    {
        if ($displayName !== null) {
            Assertion::maxLength($displayName, self::MAX_DISPLAY_NAME_LENGTH, 'HostTemplate::displayName');
        }
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setAddress(?string $address): self
    {
        if ($address !== null) {
            Assertion::maxLength($address, self::MAX_ADDRESS_LENGTH, 'HostTemplate::address');
        }
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setComment(?string $comment): self
    {
        if ($comment !== null) {
            Assertion::maxLength($comment, self::MAX_COMMENTS_LENGTH, 'HostTemplate::comment');
        }
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getParentIds(): array
    {
        return $this->parentIds;
    }

    /**
     * @param string[]|int[] $parentIds
     * @return HostTemplate
     */
    public function setParentIds(array $parentIds): HostTemplate
    {
        $parentIds = filter_var_array($parentIds, FILTER_VALIDATE_INT);
        if (is_array($parentIds)) {
            $this->parentIds = array_values(
                array_filter(
                    $parentIds,
                    function ($value) {
                        return is_int($value);
                    }
                )
            );
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return self
     */
    public function setActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     * @return HostTemplate
     */
    public function setLocked(bool $isLocked): HostTemplate
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return int
     */
    public function getActiveChecksStatus(): int
    {
        return $this->activeChecksStatus;
    }

    /**
     * @param int $activeChecksStatus
     * @return HostTemplate
     */
    public function setActiveChecksStatus(int $activeChecksStatus): HostTemplate
    {
        if (!in_array($activeChecksStatus, self::AVAILABLE_STATUS)) {
            throw HostTemplateArgumentException::badActiveChecksStatus($activeChecksStatus);
        }
        $this->activeChecksStatus = $activeChecksStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getPassiveChecksStatus(): int
    {
        return $this->passiveChecksStatus;
    }

    /**
     * @param int $passiveChecksStatus
     * @return HostTemplate
     */
    public function setPassiveChecksStatus(int $passiveChecksStatus): HostTemplate
    {
        if (!in_array($passiveChecksStatus, self::AVAILABLE_STATUS)) {
            throw HostTemplateArgumentException::badPassiveChecksStatus($passiveChecksStatus);
        }
        $this->passiveChecksStatus = $passiveChecksStatus;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxCheckAttempts(): ?int
    {
        return $this->maxCheckAttempts;
    }

    /**
     * @param int|null $maxCheckAttempts
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setMaxCheckAttempts(?int $maxCheckAttempts): HostTemplate
    {
        if ($maxCheckAttempts !== null) {
            Assertion::min($maxCheckAttempts, self::MIN_CHECK_ATTEMPS, 'HostTemplate::maxCheckAttempts');
        }
        $this->maxCheckAttempts = $maxCheckAttempts;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCheckInterval(): ?int
    {
        return $this->checkInterval;
    }

    /**
     * @param int|null $checkInterval
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setCheckInterval(?int $checkInterval): HostTemplate
    {
        if ($checkInterval !== null) {
            Assertion::min($checkInterval, self::MIN_CHECK_INTERVAL, 'HostTemplate::checkInterval');
        }
        $this->checkInterval = $checkInterval;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRetryCheckInterval(): ?int
    {
        return $this->retryCheckInterval;
    }

    /**
     * @param int|null $retryCheckInterval
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setRetryCheckInterval(?int $retryCheckInterval): HostTemplate
    {
        if ($retryCheckInterval !== null) {
            Assertion::min($retryCheckInterval, self::MIN_RETRY_CHECK_INTERVAL, 'HostTemplate::retryCheckInterval');
        }
        $this->retryCheckInterval = $retryCheckInterval;
        return $this;
    }

    /**
     * @return int
     */
    public function getNotificationsStatus(): int
    {
        return $this->notificationsStatus;
    }

    /**
     * @param int $notificationsStatus
     * @return HostTemplate
     */
    public function setNotificationsStatus(int $notificationsStatus): HostTemplate
    {
        if (!in_array($notificationsStatus, self::AVAILABLE_STATUS)) {
            HostTemplateArgumentException::badNotificationStatus($notificationsStatus);
        }
        $this->notificationsStatus = $notificationsStatus;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotificationInterval(): ?int
    {
        return $this->notificationInterval;
    }

    /**
     * @param int|null $notificationInterval
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setNotificationInterval(?int $notificationInterval): HostTemplate
    {
        if ($notificationInterval !== null) {
            Assertion::greaterOrEqualThan(
                $notificationInterval,
                self::MIN_NOTIFICATION_INTERVAL,
                'HostTemplate::notificationInterval'
            );
        }
        $this->notificationInterval = $notificationInterval;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFirstNotificationDelay(): ?int
    {
        return $this->firstNotificationDelay;
    }

    /**
     * @param int|null $firstNotificationDelay
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setFirstNotificationDelay(?int $firstNotificationDelay): HostTemplate
    {
        if ($firstNotificationDelay !== null) {
            Assertion::greaterOrEqualThan(
                $firstNotificationDelay,
                self::MIN_FIRST_NOTIFICATION_DELAY,
                'HostTemplate::firstNotificationDelay'
            );
        }
        $this->firstNotificationDelay = $firstNotificationDelay;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecoveryNotificationDelay(): ?int
    {
        return $this->recoveryNotificationDelay;
    }

    /**
     * @param int|null $recoveryNotificationDelay
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setRecoveryNotificationDelay(?int $recoveryNotificationDelay): HostTemplate
    {
        if ($recoveryNotificationDelay !== null) {
            Assertion::greaterOrEqualThan(
                $recoveryNotificationDelay,
                self::MIN_RECOVERY_NOTIFICATION_DELAY,
                'HostTemplate::recoveryNotificationDelay'
            );
        }
        $this->recoveryNotificationDelay = $recoveryNotificationDelay;
        return $this;
    }

    /**
     * @return int
     */
    public function getNotificationOptions(): int
    {
        return $this->notificationOptions;
    }

    /**
     * @param int $notificationOptions
     * @return HostTemplate
     * @throws \InvalidArgumentException
     */
    public function setNotificationOptions(int $notificationOptions): HostTemplate
    {
        $sumOfAllOptions = HostTemplate::NOTIFICATION_OPTION_DOWN
            | HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
            | HostTemplate::NOTIFICATION_OPTION_RECOVERY
            | HostTemplate::NOTIFICATION_OPTION_FLAPPING
            | HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED;
        if ($notificationOptions < 0 || ($notificationOptions & $sumOfAllOptions) !== $notificationOptions) {
            throw HostTemplateArgumentException::badNotificationOptions($notificationOptions);
        }
        $this->notificationOptions = $notificationOptions;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSnmpCommunity(): ?string
    {
        return $this->snmpCommunity;
    }

    /**
     * @param string|null $snmpCommunity
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setSnmpCommunity(?string $snmpCommunity): HostTemplate
    {
        if ($snmpCommunity !== null) {
            Assertion::maxLength($snmpCommunity, self::MAX_SNMP_COMMUNITY_LENGTH, 'HostTemplate::snmpCommunity');
        }
        $this->snmpCommunity = $snmpCommunity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSnmpVersion(): ?string
    {
        return $this->snmpVersion;
    }

    /**
     * @param string|null $snmpVersion The SNMP versions available are 1, 2c and 3.
     * @return HostTemplate
     * @throws \InvalidArgumentException
     */
    public function setSnmpVersion(?string $snmpVersion): HostTemplate
    {
        if ($snmpVersion !== null) {
            $this->snmpVersion = in_array($snmpVersion, ['1', '2c', '3'])
                ? $snmpVersion
                : null;
        }
        return $this;
    }

    /**
     * @return Image|null
     */
    public function getIcon(): ?Image
    {
        return $this->icon;
    }

    /**
     * @param Image|null $icon
     * @return HostTemplate
     */
    public function setIcon(?Image $icon): HostTemplate
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlternativeIcon(): ?string
    {
        return $this->alternativeIcon;
    }

    /**
     * @param string|null $alternativeIcon
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setAlternativeIcon(?string $alternativeIcon): HostTemplate
    {
        if ($alternativeIcon !== null) {
            Assertion::maxLength($alternativeIcon, self::MAX_ALTERNATIF_ICON_TEXT, 'HostTemplate::alternativeIcon');
        }
        $this->alternativeIcon = $alternativeIcon;
        return $this;
    }

    /**
     * @return Image|null
     */
    public function getStatusMapImage(): ?Image
    {
        return $this->statusMapImage;
    }

    /**
     * @param Image|null $statusMapImage
     * @return HostTemplate
     */
    public function setStatusMapImage(?Image $statusMapImage): HostTemplate
    {
        $this->statusMapImage = $statusMapImage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrlNotes(): ?string
    {
        return $this->urlNotes;
    }

    /**
     * @param string|null $urlNotes
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setUrlNotes(?string $urlNotes): HostTemplate
    {
        if ($urlNotes !== null) {
            Assertion::maxLength($urlNotes, self::MAX_URL_NOTES, 'HostTemplate::urlNotes');
        }
        $this->urlNotes = $urlNotes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    /**
     * @param string|null $actionUrl
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setActionUrl(?string $actionUrl): HostTemplate
    {
        if ($actionUrl !== null) {
            Assertion::maxLength($actionUrl, self::MAX_ACTION_URL, 'HostTemplate::actionUrl');
        }
        $this->actionUrl = $actionUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return HostTemplate
     * @throws \Assert\AssertionFailedException
     */
    public function setNotes(?string $notes): HostTemplate
    {
        if ($notes !== null) {
            Assertion::maxLength($notes, self::MAX_NOTES, 'HostTemplate::notes');
        }
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return int
     */
    public function getStalkingOptions(): ?int
    {
        return $this->stalkingOptions;
    }

    /**
     * @param int $stalkingOptions
     * @return HostTemplate
     * @throws \InvalidArgumentException
     */
    public function setStalkingOptions(int $stalkingOptions): HostTemplate
    {
        $sumOfAllOptions = HostTemplate::STALKING_OPTION_UP
            | HostTemplate::STALKING_OPTION_DOWN
            | HostTemplate::STALKING_OPTION_UNREACHABLE;
        if ($stalkingOptions < 0 || ($stalkingOptions & $sumOfAllOptions) !== $stalkingOptions) {
            throw HostTemplateArgumentException::badStalkingOptions($stalkingOptions);
        }
        $this->stalkingOptions = $stalkingOptions;
        return $this;
    }
}
