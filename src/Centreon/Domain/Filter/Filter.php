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
    private $id;

    /**
     * @var string|null Name of the filter
     */
    private $name;

    /**
     * @var int|null User id
     */
    private $userId;

    /**
     * @var string|null Page name
     */
    private $pageName;

    /**
     * @var FilterCriteria[] Criterias
     */
    private $criterias = [];

    /**
     * @var int|null Order
     */
    private $order;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Filter
     */
    public function setId(?int $id): Filter
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
     * @return Filter
     */
    public function setName(?string $name): Filter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     * @return Filter
     */
    public function setUserId(?int $userId): Filter
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPageName(): ?string
    {
        return $this->pageName;
    }

    /**
     * @param string|null $pageName
     * @return Filter
     */
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
     * @return Filter
     */
    public function setCriterias(array $criterias): Filter
    {
        $this->criterias = $criterias;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     * @return Filter
     */
    public function setOrder(?int $order): Filter
    {
        $this->order = $order;
        return $this;
    }
}
