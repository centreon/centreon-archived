<?php


namespace Centreon\Domain\Entity\Configuration;

class Poller
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $ip;
    /**
     * @var bool
     */
    private $isLocalhost;
    /**
     * @var bool
     */
    private $isActivate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Poller
     */
    public function setId(int $id): Poller
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Poller
     */
    public function setName(string $name): Poller
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Poller
     */
    public function setIp(string $ip): Poller
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocalhost(): bool
    {
        return $this->isLocalhost;
    }

    /**
     * @param bool $isLocalhost
     * @return Poller
     */
    public function setLocalhost(bool $isLocalhost): Poller
    {
        $this->isLocalhost = $isLocalhost;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return Poller
     */
    public function setActivate(bool $isActivate): Poller
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
