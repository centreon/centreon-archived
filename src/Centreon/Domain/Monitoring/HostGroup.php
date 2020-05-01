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
 * Class representing a record of a host group in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class HostGroup implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MAIN = 'hg_main';
    public const SERIALIZER_GROUP_WITH_HOST = 'hg_with_host';

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
            'hostgroup_id' => 'setId',
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
     * @return HostGroup
     */
    public function setId(int $id): HostGroup
    {
        $this->id = $id;
        return $this;
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
     * @return HostGroup
     */
    public function setName(?string $name): HostGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Host $host
     * @return HostGroup
     */
    public function addHost(Host $host): HostGroup
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
     * Indicates if a host exists in this host group.
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
     * @param Host[] $hosts
     * @return HostGroup
     */
    public function setHosts(array $hosts): HostGroup
    {
        $this->hosts = $hosts;
        return $this;
    }
}
