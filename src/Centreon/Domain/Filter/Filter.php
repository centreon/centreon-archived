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

namespace Centreon\Domain\Filter;

/**
 * This class is designed to represent a filter entity.
 *
 * @package Centreon\Domain\Filter
 */
class Filter
{
    /**
     * @var int|null Unique id of the filter
     */
    private ?int $id = null;

    /**
     * @var string|null Name of the filter
     */
    private ?string $name = null;

    /**
     * @var int|null User id
     */
    private ?int $userId = null;

    /**
     * @var string|null Page name
     */
    private ?string $pageName = null;

    /**
     * @var FilterCriteria[] Criterias
     */
    private array $criterias = [];

    /**
     * @var int|null Order
     */
    private ?int $order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Filter
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Filter
    {
        $this->name = $name;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): Filter
    {
        $this->userId = $userId;
        return $this;
    }

    public function getPageName(): ?string
    {
        return $this->pageName;
    }

    public function setPageName(?string $pageName): Filter
    {
        $this->pageName = $pageName;
        return $this;
    }

    /**
     * @return FilterCriteria[]
     */
    public function getCriterias(): array
    {
        return $this->criterias;
    }

    /**
     * @param FilterCriteria[] $criterias
     */
    public function setCriterias(array $criterias): Filter
    {
        $this->criterias = $criterias;
        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): Filter
    {
        $this->order = $order;
        return $this;
    }
}
