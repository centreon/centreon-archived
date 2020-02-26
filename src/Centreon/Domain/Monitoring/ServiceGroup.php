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

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

/**
 * Class representing a record of a service group in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class ServiceGroup implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MAIN = 'sg_main';
    public const SERIALIZER_GROUP_WITH_HOST = 'sg_with_host';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @var string|null
     */
    private $name;

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'servicegroup_id' => 'setId',
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ServiceGroup
     */
    public function setId(int $id): ServiceGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Host $host
     * @return ServiceGroup
     */
    public function addHost(Host $host): ServiceGroup
    {
        $this->hosts[] = $host;
        return $this;
    }


    /**
     * @return Host[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * @param Host[] $hosts
     * @return ServiceGroup
     */
    public function setHosts(array $hosts): ServiceGroup
    {
        $this->hosts = $hosts;
        return $this;
    }

    /**
     * Indicates if a host exists in this service group.
     *
     * @param int $hostId Host id to find
     * @return bool
     */
    public function isHostExists(int $hostId): bool
    {
        foreach ($this->hosts as $host) {
            if ($host->getId() === $hostId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ServiceGroup
     */
    public function setName(?string $name): ServiceGroup
    {
        $this->name = $name;
        return $this;
    }
}
