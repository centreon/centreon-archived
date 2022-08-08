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

namespace Centreon\Domain\ActionLog;

class ActionLog
{
    public const ACTION_TYPE_ADD = 'a';
    public const ACTION_TYPE_DELETE = 'd';
    public const ACTION_TYPE_ENABLE = 'enable';
    public const ACTION_TYPE_DISABLE = 'disable';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTime|null
     */
    private $creationDate;

    /**
     * @var string
     */
    private $objectType;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var string
     */
    private $objectName;

    /**
     * @var string
     */
    private $actionType;

    /**
     * @var int Id of the contact who added this action log
     */
    private $contactId;

    /**
     * ActionLog constructor.
     *
     * @param string $objectType Object type (ex: host, service)
     * @param int $objectId Object id
     * @param string $objectName Object name (ex: localhost, localhost/ping)
     * @param string $actionType Action type (see ActionLog::ACTION_TYPE_ADD, etc...)
     * @param int $contactId Id of the contact who added this action log
     * @param \DateTime|null $creationDate If null, the creation date will be the same as the entity's creation date.
     *
     * @see ActionLog::ACTION_TYPE_ADD
     * @see ActionLog::ACTION_TYPE_DELETE
     * @see ActionLog::ACTION_TYPE_ENABLE
     * @see ActionLog::ACTION_TYPE_DISABLE
     */
    public function __construct(
        string $objectType,
        int $objectId,
        string $objectName,
        string $actionType,
        int $contactId,
        \DateTime $creationDate = null
    ) {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->objectName = $objectName;
        $this->actionType = $actionType;
        $this->contactId = $contactId;

        if ($creationDate === null) {
            $this->creationDate = new \DateTime();
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ActionLog
     */
    public function setId(?int $id): ActionLog
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime|null $creationDate
     * @return ActionLog
     */
    public function setCreationDate(?\DateTime $creationDate): ActionLog
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     * @return ActionLog
     */
    public function setObjectType(string $objectType): ActionLog
    {
        $this->objectType = $objectType;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     * @return ActionLog
     */
    public function setObjectId(int $objectId): ActionLog
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * @param string $objectName
     * @return ActionLog
     */
    public function setObjectName(string $objectName): ActionLog
    {
        $this->objectName = $objectName;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * @param string $actionType
     * @return ActionLog
     */
    public function setActionType(string $actionType): ActionLog
    {
        $allowedActionTypes = [
            self::ACTION_TYPE_ADD,
            self::ACTION_TYPE_DELETE,
            self::ACTION_TYPE_ENABLE,
            self::ACTION_TYPE_DISABLE
        ];
        if (!in_array($actionType, $allowedActionTypes)) {
            throw new \InvalidArgumentException(_('Type of action not recognized'));
        }
        $this->actionType = $actionType;
        return $this;
    }

    /**
     * @return int
     */
    public function getContactId(): int
    {
        return $this->contactId;
    }

    /**
     * @param int $contactId
     * @return ActionLog
     */
    public function setContactId(int $contactId): ActionLog
    {
        $this->contactId = $contactId;
        return $this;
    }
}
