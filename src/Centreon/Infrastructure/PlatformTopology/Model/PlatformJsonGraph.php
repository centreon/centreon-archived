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

namespace Centreon\Infrastructure\PlatformTopology\Model;

use Centreon\Domain\PlatformTopology\Platform;
use Centreon\Domain\PlatformTopology\PlatformRelation;

/**
 * Format Platform to fit the JSON Graph Schema specification
 * @link https://github.com/jsongraph/json-graph-specification
 */
class PlatformJsonGraph
{
    /**
     * @var string|null Stringified Platform Id
     */
    private $id;

    /**
     * @var string|null Platform type
     */
    private $type;

    /**
     * @var string|null Platform Name
     */
    private $label;

    /**
     * @var array Custom properties of a Json Graph Object
     */
    private $metadata = [];

    /**
     * @var array relation details between a platform and its parent
     */
    private $relation = [];

    public function __construct(Platform $platform)
    {
        $this->setId((string) $platform->getId());
        $this->setType($platform->getType());
        $this->setLabel($platform->getName());
        if ($platform->getRelation() !== null) {
            $this->setRelation($platform->getRelation());
        }

        $metadata = [];
        if ($platform->getServerId() !== null) {
            $metadata['centreon-id'] = (string) $platform->getServerId();
        }
        if ($platform->getHostname() !== null) {
            $metadata['hostname'] = $platform->getHostname();
        }
        if ($platform->getAddress() !== null) {
            $metadata['address'] = $platform->getAddress();
        }
        $this->setMetadata($metadata);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return self
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return self
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelation(): array
    {
        return $this->relation;
    }

    /**
     * @param array $relation
     * @return self
     */
    public function setRelation(PlatformRelation $platformRelation): self
    {
        $this->relation = [
            'source' => (string) $platformRelation->getSource(),
            'relation' => $platformRelation->getRelation(),
            'target' => (string) $platformRelation->getTarget(),
        ];
        return $this;
    }
}
