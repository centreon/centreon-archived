<?php
declare(strict_types=1);

namespace Centreon\Domain\Monitoring;

use JMS\Serializer\Annotation as Serializer;

class Service
{
    /**
     * @Serializer\Groups({"Default", "service_main", "service_full"})
     * @var int Unique index
     */
    private $id;

    /**
     * @Serializer\Groups({"Default", "service_main", "service_full"})
     * @var Host
     */
    private $host;

    /**
     * @Serializer\Groups({"Default", "service_main", "service_full"})
     * @var string
     */
    private $description;

    /**
     * @Serializer\Groups({"Default", "service_main", "service_full"})
     * @var string
     */
    private $displayName;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var bool
     */
    private $isAcknowledged;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var int
     */
    private $acknowledgementType;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var bool
     */
    private $isActiveCheck;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var int
     */
    private $checkAttempt;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var int
     */
    private $maxCheckAttempt;

    /**
     * @var bool
     * @Serializer\Groups({"service_full"})
     */
    private $isChecked;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var int
     */
    private $state;

    /**
     * @Serializer\Groups({"service_main", "service_full"})
     * @var string
     */
    private $output;

    /**
     * @var \DateTime
     * @Serializer\Groups({"service_full"})
     */
    private $lastCheck;

    /**
     * @var \DateTime
     * @Serializer\Groups({"service_full"})
     */
    private $nextCheck;

    /**
     * @var \DateTime
     * @Serializer\Groups({"service_full"})
     */
    private $lastUpdate;

    /**
     * @var \DateTime
     * @Serializer\Groups({"service_full"})
     */
    private $lastStateChange;

    /**
     * @var \DateTime
     * @Serializer\Groups({"service_full"})
     */
    private $lastHardStateChange;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Service
     */
    public function setId(int $id): Service
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Service
     */
    public function setDescription(string $description): Service
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     * @return Service
     */
    public function setDisplayName(string $displayName): Service
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcknowledged(): bool
    {
        return $this->isAcknowledged;
    }

    /**
     * @param bool $isAcknowledged
     * @return Service
     */
    public function setAcknowledged(bool $isAcknowledged): Service
    {
        $this->isAcknowledged = $isAcknowledged;
        return $this;
    }

    /**
     * @return int
     */
    public function getAcknowledgementType(): int
    {
        return $this->acknowledgementType;
    }

    /**
     * @param int $acknowledgementType
     * @return Service
     */
    public function setAcknowledgementType(int $acknowledgementType): Service
    {
        $this->acknowledgementType = $acknowledgementType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActiveCheck(): bool
    {
        return $this->isActiveCheck;
    }

    /**
     * @param bool $isActiveCheck
     * @return Service
     */
    public function setActiveCheck(bool $isActiveCheck): Service
    {
        $this->isActiveCheck = $isActiveCheck;
        return $this;
    }

    /**
     * @return int
     */
    public function getCheckAttempt(): int
    {
        return $this->checkAttempt;
    }

    /**
     * @param int $checkAttempt
     * @return Service
     */
    public function setCheckAttempt(int $checkAttempt): Service
    {
        $this->checkAttempt = $checkAttempt;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxCheckAttempt(): int
    {
        return $this->maxCheckAttempt;
    }

    /**
     * @param int $maxCheckAttempt
     * @return Service
     */
    public function setMaxCheckAttempt(int $maxCheckAttempt): Service
    {
        $this->maxCheckAttempt = $maxCheckAttempt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->isChecked;
    }

    /**
     * @param bool $isChecked
     * @return Service
     */
    public function setChecked(bool $isChecked): Service
    {
        $this->isChecked = $isChecked;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Service
     */
    public function setState(int $state): Service
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return Service
     */
    public function setOutput(string $output): Service
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastCheck(): \DateTime
    {
        return $this->lastCheck;
    }

    /**
     * @param \DateTime $lastCheck
     * @return Service
     */
    public function setLastCheck(\DateTime $lastCheck): Service
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNextCheck(): \DateTime
    {
        return $this->nextCheck;
    }

    /**
     * @param \DateTime $nextCheck
     * @return Service
     */
    public function setNextCheck(\DateTime $nextCheck): Service
    {
        $this->nextCheck = $nextCheck;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate(): \DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime $lastUpdate
     * @return Service
     */
    public function setLastUpdate(\DateTime $lastUpdate): Service
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastStateChange(): \DateTime
    {
        return $this->lastStateChange;
    }

    /**
     * @param \DateTime $lastStateChange
     * @return Service
     */
    public function setLastStateChange(\DateTime $lastStateChange): Service
    {
        $this->lastStateChange = $lastStateChange;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastHardStateChange(): \DateTime
    {
        return $this->lastHardStateChange;
    }

    /**
     * @param \DateTime $lastHardStateChange
     * @return Service
     */
    public function setLastHardStateChange(\DateTime $lastHardStateChange): Service
    {
        $this->lastHardStateChange = $lastHardStateChange;
        return $this;
    }

    /**
     * @return Host
     */
    public function getHost(): Host
    {
        return $this->host;
    }

    /**
     * @param Host $host
     * @return Service
     */
    public function setHost(Host $host): Service
    {
        $this->host = $host;
        return $this;
    }
}
