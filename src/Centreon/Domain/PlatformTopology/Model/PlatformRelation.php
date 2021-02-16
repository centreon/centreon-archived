<?php

/*
 *
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\PlatformTopology\Model;

/**
 * Class designed to represent a relation between two platforms
 */
class PlatformRelation
{

    /**
     * Broker relation types
     */
    public const NORMAL_RELATION = 'normal';
    public const PEER_RETENTION_RELATION = 'peer_retention';

    /**
     * Available relation types
     */
    private const AVAILABLE_RELATIONS = [
        self::NORMAL_RELATION,
        self::PEER_RETENTION_RELATION
    ];
    /**
     * Source node in relation
     *
     * @var int
     */
    private $source;

    /**
     * Broker relation
     *
     * @var string
     */
    private $relation;

    /**
     * Target node in relation
     *
     * @var int
     */
    private $target;

    /**
     * Get the value of source
     *
     * @return  int
     */
    public function getSource(): int
    {
        return $this->source;
    }

    /**
     * Set the value of source
     *
     * @param int $source
     *
     * @return  self
     */
    public function setSource(int $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the value of relation
     *
     * @return  string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * Set the value of relation
     *
     * @param string|null $relation
     *
     * @return  self
     */
    public function setRelation(?string $relation): self
    {
        //Set relation to normal if invalid relation type is given to be able to compute the relation
        if (null !== $relation && !in_array($relation, self::AVAILABLE_RELATIONS)) {
            $this->relation = self::NORMAL_RELATION;
        } else {
            $this->relation = $relation;
        }

        return $this;
    }

    /**
     * Get the value of target
     *
     * @return  int
     */
    public function getTarget(): int
    {
        return $this->target;
    }

    /**
     * Set the value of target
     *
     * @param int $target
     *
     * @return  self
     */
    public function setTarget(int $target): self
    {
        $this->target = $target;

        return $this;
    }
}
