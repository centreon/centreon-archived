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

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Monitoring\Interfaces\EventObjectInterface;

/**
 * Class AckEventObject
 * This class represents an Acknowledgement Event for Timeline bulk listing of different events
 * @package Centreon\Domain\Monitoring\Entity
 */
class AckEventObject extends Acknowledgement implements EventObjectInterface
{
    public const EVENTTYPE = 'A';

    // Groups for serializing
    public const SERIALIZER_GROUP_LIST = 'ack_event_list';
    public const SERIALIZER_GROUP_FULL = 'ack_event_full';

    /**
     * Author name
     * @var string|null
     */
    private $author;

    /**
     * @return null|string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param null|string $author
     */
    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->getEntryTime();
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
            'output' => 'setComment',
            'status' => 'setState',
            'notify_contacts' => 'setNotifyContacts',
            'type' => 'setType',
            'persistent' => 'setPersistentComment',
            'deletion_time' => 'setDeletionTime',
            'sticky' => 'setSticky',
            'timestamp' => 'setEntryTime',
            'contact' => 'setAuthor',
        ];
    }
}
