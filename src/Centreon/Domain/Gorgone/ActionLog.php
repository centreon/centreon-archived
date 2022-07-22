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

namespace Centreon\Domain\Gorgone;

/**
 * This class is designed to represent a action log received by the Gorgone server.
 *
 * A command can produce more than one action log.
 *
 * @package Centreon\Domain\Gorgone
 */
class ActionLog
{
    /**
     * @var \DateTime Creation time of the response
     */
    private ?\DateTime $creationTime = null;

    /**
     * @var \DateTime Event time of the response
     */
    private ?\DateTime $eventTime = null;

    /**
     * @var int Id of the action log
     */
    private ?int $id = null;

    /**
     * @var int Status code of the response
     * @see ResponseInterface::STATUS_BEGIN for code when action begin
     * @see ResponseInterface::STATUS_ERROR for code when there is an error
     * @see ResponseInterface::STATUS_OK for code when the last action log has been received and its statut is OK
     */
    private ?int $code = null;

    /**
     * @var string Response data
     */
    private ?string $data = null;

    /**
     * Factory to create a action log based on the Gorgone response.
     *
     * @param array<string, string> $details Details used to create an action log
     * @throws \Exception
     */
    public static function create(array $details): ActionLog
    {
        if (empty($details['token'])) {
            throw new \LogicException('Token can not empty, null or not defined');
        }
        return (new ActionLog($details['token']))
            ->setId((int) ($details['id'] ?? 0))
            ->setCode((int) ($details['code'] ?? 0))
            ->setCreationTime((new \DateTime())->setTimestamp((int) ($details['ctime'] ?? 0)))
            ->setEventTime((new \DateTime())->setTimestamp((int) ($details['etime'] ?? 0)))
            ->setData($details['data'] ?? '{}');
    }

    /**
     * @param string $token
     * @see ActionLog::$token
     */
    public function __construct(private readonly string $token)
    {
    }

    /**
     * @see ActionLog::$creationTime
     */
    public function getCreationTime(): \DateTime
    {
        return $this->creationTime;
    }

    /**
     * @see ActionLog::$creationTime
     */
    public function setCreationTime(\DateTime $creationTime): ActionLog
    {
        $this->creationTime = $creationTime;
        return $this;
    }

    /**
     * @see ActionLog::$eventTime
     */
    public function getEventTime(): \DateTime
    {
        return $this->eventTime;
    }

    /**
     * @see ActionLog::$eventTime
     */
    public function setEventTime(\DateTime $eventTime): ActionLog
    {
        $this->eventTime = $eventTime;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): ActionLog
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @see ActionLog::$token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @see ActionLog::$code
     */
    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): ActionLog
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @see ActionLog::$data
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @see ActionLog::$data
     */
    public function setData(string $data): ActionLog
    {
        $this->data = $data;
        return $this;
    }
}
