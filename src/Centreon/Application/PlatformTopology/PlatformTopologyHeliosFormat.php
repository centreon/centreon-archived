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

namespace Centreon\Application\PlatformTopology;

use Centreon\Domain\PlatformTopology\PlatformTopology;

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class PlatformTopologyHeliosFormat
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $label;

    /**
     * @var array|null
     */
    private $metadata;

    /**
     * @var array|null
     */
    private $relation;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getRelation(): ?array
    {
        return $this->relation;
    }

    public function setRelation(array $relation): self
    {
        $relationStringified = [];
        foreach($relation as $name => $relationItem) {
            if($relationItem !== null) {
                $relationStringified[$name] = (string) $relationItem;
            }else{
                $relationStringified[$name] = null;
            }
        }
        $this->relation = $relationStringified;
        return $this;
    }

    public function __construct(PlatformTopology $platformTopology)
    {
        $this->setId((string) $platformTopology->getId());
        $this->setType($platformTopology->getType());
        $this->setLabel($platformTopology->getName());
        $this->setRelation($platformTopology->getRelation());

        $metadata = [];
        if ($platformTopology->getServerId() !== null){
            $metadata['centreon-id'] = (string) $platformTopology->getServerId();
        }
        if ($platformTopology->getHostname !== null){
            $metadata['hostname'] = $platformTopology->getHostname();
        }
        $this->setMetadata($metadata);
    }
}