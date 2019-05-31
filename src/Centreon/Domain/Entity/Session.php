<?php
declare(strict_types=1);

namespace Centreon\Domain\Entity;


class Session
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var \DateTime
     */
    private $lastReload;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Session
     */
    public function setId(int $id): Session
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return Session
     */
    public function setSessionId(string $sessionId): Session
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return Session
     */
    public function setUserId(int $userId): Session
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastReload(): \DateTime
    {
        return $this->lastReload;
    }

    /**
     * @param \DateTime $lastReload
     * @return Session
     */
    public function setLastReload(\DateTime $lastReload): Session
    {
        $this->lastReload = $lastReload;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return Session
     */
    public function setIpAddress(string $ipAddress): Session
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }
}
