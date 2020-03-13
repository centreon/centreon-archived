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

namespace Centreon\Domain\Monitoring\Model;

/**
 * Class Log
 */
class Log
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime|null
     */
    private $createTime;

    /**
     * @var int|null
     */
    private $hostId;

    /**
     * @var string|null
     */
    private $hostName;

    /**
     * @var string|null
     */
    private $instanceName;

    /**
     * @var int|null
     */
    private $issueId;

    /**
     * @var int|null
     */
    private $msgType;

    /**
     * @var string|null
     */
    private $notificationCmd;

    /**
     * @var string|null
     */
    private $notificationContact;

    /**
     * @var string|null
     */
    private $output;

    /**
     * @var int|null
     */
    private $retry;

    /**
     * @var string|null
     */
    private $serviceDescription;

    /**
     * @var int|null
     */
    private $serviceId;

    /**
     * @var int|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $type;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime|null $createTime
     */
    public function setCreateTime(?\DateTime $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * @return int|null
     */
    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    /**
     * @param int|null $hostId
     */
    public function setHostId(?int $hostId): void
    {
        $this->hostId = $hostId;
    }

    /**
     * @return null|string
     */
    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    /**
     * @param null|string $hostName
     */
    public function setHostName(?string $hostName): void
    {
        $this->hostName = $hostName;
    }

    /**
     * @return null|string
     */
    public function getInstanceName(): ?string
    {
        return $this->instanceName;
    }

    /**
     * @param string $instanceName
     */
    public function setInstanceName(?string $instanceName): void
    {
        $this->instanceName = $instanceName;
    }

    /**
     * @return int|null
     */
    public function getIssueId(): ?int
    {
        return $this->issueId;
    }

    /**
     * @param int|null $issueId
     */
    public function setIssueId(?int $issueId): void
    {
        $this->issueId = $issueId;
    }

    /**
     * @return int|null
     */
    public function getMsgType(): ?int
    {
        return $this->msgType;
    }

    /**
     * @param int|null $msgType
     */
    public function setMsgType(?int $msgType): void
    {
        $this->msgType = $msgType;
    }

    /**
     * @return null|string
     */
    public function getNotificationCmd(): ?string
    {
        return $this->notificationCmd;
    }

    /**
     * @param null|string $notificationCmd
     */
    public function setNotificationCmd(?string $notificationCmd): void
    {
        $this->notificationCmd = $notificationCmd;
    }

    /**
     * @return null|string
     */
    public function getNotificationContact(): ?string
    {
        return $this->notificationContact;
    }

    /**
     * @param null|string $notificationContact
     */
    public function setNotificationContact(?string $notificationContact): void
    {
        $this->notificationContact = $notificationContact;
    }

    /**
     * @return null|string
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @param null|string $output
     */
    public function setOutput(?string $output): void
    {
        $this->output = $output;
    }

    /**
     * @return int|null
     */
    public function getRetry(): ?int
    {
        return $this->retry;
    }

    /**
     * @param int|null $retry
     */
    public function setRetry(?int $retry): void
    {
        $this->retry = $retry;
    }

    /**
     * @return null|string
     */
    public function getServiceDescription(): ?string
    {
        return $this->serviceDescription;
    }

    /**
     * @param null|string $serviceDescription
     */
    public function setServiceDescription(?string $serviceDescription): void
    {
        $this->serviceDescription = $serviceDescription;
    }

    /**
     * @return int|null
     */
    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    /**
     * @param int|null $serviceId
     */
    public function setServiceId(?int $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }
}
