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


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
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

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }
}
