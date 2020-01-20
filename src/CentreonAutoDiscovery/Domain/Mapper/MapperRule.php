<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Domain\Mapper;

/**
 * This class is designed to manage the mappers to apply to a auto discovery job
 * before creating the discovered hosts.
 *
 * @package CentreonAutoDiscovery\Domain\Mapper
 */
class MapperRule
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $jobId;

    /**
     * @var int
     */
    private $order;

    /**
     * @var string Name of the modifier applied
     */
    private $name;

    /**
     * @var string Details used by the modifier
     * @JMS\Serializer\Annotation\Accessor(getter="detailsToArray")
     */
    private $details;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MapperRule
     */
    public function setId(int $id): MapperRule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * @param int $jobId
     * @return MapperRule
     */
    public function setJobId(int $jobId): MapperRule
    {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return MapperRule
     */
    public function setOrder(int $order): MapperRule
    {
        $this->order = $order;
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
     * @return MapperRule
     */
    public function setName(string $name): MapperRule
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return MapperRule
     */
    public function setDetails(string $details): MapperRule
    {
        $this->details = $details;
        return $this;
    }

    public function detailsToArray(): array
    {
        return json_decode($this->details, true) ?? [];
    }
}
