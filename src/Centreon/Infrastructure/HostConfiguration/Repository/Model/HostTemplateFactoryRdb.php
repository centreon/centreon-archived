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

namespace Centreon\Infrastructure\HostConfiguration\Repository\Model;

use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\Media\Model\Image;
use Centreon\Domain\HostConfiguration\Exception\HostTemplateFactoryException;

/**
 * This class is designed to provide a way to create the HostTemplate entity from the database.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Model
 */
class HostTemplateFactoryRdb
{
    /**
     * Create a HostTemplate entity from database data.
     *
     * @param array<string, mixed> $data
     * @return HostTemplate
     * @throw \InvalidArgumentException
     * @throws HostTemplateFactoryException|\Assert\AssertionFailedException
     */
    public static function create(array $data): HostTemplate
    {
        $hostTemplate = new HostTemplate();
        if (isset($data['icon_id'])) {
            $hostTemplate->setIcon(
                (new Image())
                    ->setId((int) $data['icon_id'])
                    ->setName($data['icon_name'])
                    ->setComment($data['icon_comment'])
                    ->setPath(str_replace('//', '/', ($data['icon_path'])))
            );
        }
        if (isset($data['smi_id'])) {
            $hostTemplate->setStatusMapImage(
                (new Image())
                    ->setId((int) $data['smi_id'])
                    ->setName($data['smi_name'])
                    ->setComment($data['smi_comment'])
                    ->setPath(str_replace('//', '/', $data['smi_path']))
            );
        }

        $hostTemplate
            ->setId((int) $data['host_id'])
            ->setName($data['host_name'])
            ->setAlias($data['host_alias'])
            ->setDisplayName($data['display_name'])
            ->setAddress($data['host_address'])
            ->setLocked((bool) $data['host_locked'])
            ->setActivated((bool) $data['host_activate'])
            ->setComment($data['host_comment'])
            ->setActionUrl($data['ehi_action_url'])
            ->setNotes($data['ehi_notes'])
            ->setUrlNotes($data['ehi_notes_url'])
            ->setActionUrl($data['ehi_action_url'])
            ->setActiveChecksStatus((int) $data['host_active_checks_enabled'])
            ->setPassiveChecksStatus((int) $data['host_passive_checks_enabled'])
            ->setFirstNotificationDelay(self::getIntOrNull($data['host_first_notification_delay']))
            ->setRecoveryNotificationDelay(self::getIntOrNull($data['host_recovery_notification_delay']))
            ->setMaxCheckAttempts(self::getIntOrNull($data['host_max_check_attempts']))
            ->setCheckInterval(self::getIntOrNull($data['host_check_interval']))
            ->setRetryCheckInterval(self::getIntOrNull($data['host_retry_check_interval']))
            ->setNotificationOptions(self::convertNotificationOptions($data['host_notification_options']))
            ->setNotificationInterval(self::getIntOrNull($data['host_notification_interval']))
            ->setStalkingOptions(self::convertStalkingOptions($data['host_stalking_options']))
            ->setSnmpCommunity($data['host_snmp_community'])
            ->setSnmpVersion($data['host_snmp_version'])
            ->setComment($data['host_comment']);

        if (!empty($data['parents'])) {
            $hostTemplate->setParentIds(explode(',', $data['parents']));
        }
        return $hostTemplate;
    }

    /**
     * @param int|string|null $property
     * @return int|null
     */
    private static function getIntOrNull($property): ?int
    {
        return ($property !== null) ? (int) $property : null;
    }

    /**
     * Convert the notification options from string to integer.
     *
     * HostTemplate::NOTIFICATION_OPTION_DOWN                => d<br>
     * HostTemplate::NOTIFICATION_OPTION_UNREACHABLE         => u<br>
     * HostTemplate::NOTIFICATION_OPTION_RECOVERY            => r<br>
     * HostTemplate::NOTIFICATION_OPTION_FLAPPING            => f<br>
     * HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED  => s<br>
     * HostTemplate::NOTIFICATION_OPTION_NONE                => n
     *
     * @param string|null $options
     * @return int
     * @throws HostTemplateFactoryException
     */
    private static function convertNotificationOptions(?string $options): int
    {
        if (empty($options)) {
            // The null value corresponds to all options
            return HostTemplate::NOTIFICATION_OPTION_DOWN
                | HostTemplate::NOTIFICATION_OPTION_UNREACHABLE
                | HostTemplate::NOTIFICATION_OPTION_RECOVERY
                | HostTemplate::NOTIFICATION_OPTION_FLAPPING
                | HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED;
        }
        $optionToDefine = 0;
        $optionsTags = explode(',', $options);
        $optionsNotAllowed = array_diff($optionsTags, ['d','u','r','f','s',]);
        if (!empty($optionsNotAllowed)) {
            throw HostTemplateFactoryException::notificationOptionsNotAllowed(implode(',', $optionsNotAllowed));
        }
        if (in_array('n', $optionsTags)) {
            $optionToDefine = 0;
        } else {
            if (in_array('d', $optionsTags)) {
                $optionToDefine |= HostTemplate::NOTIFICATION_OPTION_DOWN;
            }
            if (in_array('u', $optionsTags)) {
                $optionToDefine |= HostTemplate::NOTIFICATION_OPTION_UNREACHABLE;
            }
            if (in_array('r', $optionsTags)) {
                $optionToDefine |= HostTemplate::NOTIFICATION_OPTION_RECOVERY;
            }
            if (in_array('f', $optionsTags)) {
                $optionToDefine |= HostTemplate::NOTIFICATION_OPTION_FLAPPING;
            }
            if (in_array('s', $optionsTags)) {
                $optionToDefine |= HostTemplate::NOTIFICATION_OPTION_DOWNTIME_SCHEDULED;
            }
        }
        return $optionToDefine;
    }

    /**
     * Converts the stalking options from string to integer.
     *
     * HostTemplate::STALKING_OPTION_UP           => o<br>
     * HostTemplate::STALKING_OPTION_DOWN         => d<br>
     * HostTemplate::STALKING_OPTION_UNREACHABLE  => u
     *
     * @param string|null $options
     * @return int
     * @throws HostTemplateFactoryException
     */
    private static function convertStalkingOptions(?string $options): int
    {
        if (empty($options)) {
            return 0;
        }
        $optionToDefine = 0;
        $optionsTags = explode(',', $options);
        $optionsNotAllowed = array_diff($optionsTags, ['o','d','u']);
        if (!empty($optionsNotAllowed)) {
            throw HostTemplateFactoryException::stalkingOptionsNotAllowed(implode(',', $optionsNotAllowed));
        }
        if (in_array('o', $optionsTags)) {
            $optionToDefine |= HostTemplate::STALKING_OPTION_UP;
        }
        if (in_array('d', $optionsTags)) {
            $optionToDefine |= HostTemplate::STALKING_OPTION_DOWN;
        }
        if (in_array('u', $optionsTags)) {
            $optionToDefine |= HostTemplate::STALKING_OPTION_UNREACHABLE;
        }
        return $optionToDefine;
    }
}
