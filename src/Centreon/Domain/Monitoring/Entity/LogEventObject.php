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

namespace Centreon\Domain\Monitoring\Entity;

use Centreon\Domain\Monitoring\Interfaces\EventObjectInterface;
use Centreon\Domain\Monitoring\Model\Log as BaseLog;
use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

/**
 * Class LogEventObject
 * @package Centreon\Domain\Monitoring
 */
class LogEventObject extends BaseLog implements EventObjectInterface, EntityDescriptorMetadataInterface
{
    public const EVENTTYPE = 'L';

    // Groups for serializing
    public const SERIALIZER_GROUP_LIST = 'log_event_list';
    public const SERIALIZER_GROUP_FULL = 'log_event_full';

    /**
     * @inheritdoc
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->getCreateTime();
    }

    /**
     * @inheritdoc
     */
    public function getEventType(): string
    {
        return self::EVENTTYPE;
    }

    /**
     * @inheritdoc
     */
    public function getEventId(): string
    {
        return self::EVENTTYPE . $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'id' => 'setId',
            'output' => 'setOutput',
            'timestamp' => 'setCreateTime',
            'status' => 'setStatus',
            'type' => 'setType',
            'retry' => 'setRetry',
            'contact' => 'setNotificationContact',
            'command' => 'setNotificationCmd'
        ];
    }

    /**
     * Convert integer status to text
     * @return null|string
     */
    public function getStatusText(): ?string
    {
        switch ($this->getStatus()) {
            case 0:
                $textValue = 'OK';
                break;
            case 1:
                $textValue = 'WARNING';
                break;
            case 2:
                $textValue = 'CRITICAL';
                break;
            case 3:
                $textValue = 'UNKNOWN';
                break;
            default:
                $textValue = null;
                break;
        }

        return $textValue;
    }

    /**
     * Convert integer type to text
     * @return null|string
     */
    public function getTypeText(): ?string
    {
        switch ($this->getType()) {
            case 0:
                $textValue = 'SOFT';
                break;
            case 1:
                $textValue = 'HARD';
                break;
            default:
                $textValue = null;
                break;
        }

        return $textValue;
    }
}
