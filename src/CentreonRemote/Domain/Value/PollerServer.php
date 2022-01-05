<?php

namespace CentreonRemote\Domain\Value;

class PollerServer
{
    /**
     * @var int $id the poller id
     */
    private $id;

    /**
     * @var string $name the poller name
     */
    private $name;

    /**
     * @var string $ip the poller ip address
     */
    private $ip;


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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Get poller name
     *
     * @return string the poller name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set poller name
     *
     * @param string $name the poller name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     */
    public function setIp($ip): void
    {
        $this->ip = $ip;
    }
}
