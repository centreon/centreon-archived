<?php


namespace Centreon\Domain\Entity;

class Service
{
    /**
     * @var int Unique index
     */
    private $id;

    /**
     * @var int Host id
     */
    private $hostId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var bool
     */
    private $isAcknowledged;

    /**
     * @var int
     */
    private $acknowledgementType;

    /**
     * @var bool
     */
    private $isActiveCheck;

    /**
     * @var int
     */
    private $checkAttempt;

    /**
     * @var int
     */
    private $maxCheckAttempt;

    /**
     * @var bool
     */
    private $isChecked;

    /**
     * @var int
     */
    private $state;

    /**
     * @var string
     */
    private $output;

    /**
     * @var \DateTime
     */
    private $lastCheck;

    /**
     * @var \DateTime
     */
    private $nextCheck;

    /**
     * @var \DateTime
     */
    private $lastUpdate;

    /**
     * @var \DateTime
     */
    private $lastStateChange;

    /**
     * @var \DateTime
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
     * @return int
     */
    public function getHostId(): int
    {
        return $this->hostId;
    }

    /**
     * @param int $hostId
     * @return Service
     */
    public function setHostId(int $hostId): Service
    {
        $this->hostId = $hostId;
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
    public function setIsAcknowledged(bool $isAcknowledged): Service
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
}
