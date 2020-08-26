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

namespace Centreon\Domain\ServiceConfiguration;

/**
 * This class is designed to represent extended service information.
 *
 * @package Centreon\Domain\ServiceConfiguration
 * @see Service
 */
class ExtendedService
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $notes;

    /**
     * @var string|null
     */
    private $notesUrl;

    /**
     * @var string|null
     */
    private $actionUrl;

    /**
     * @var int|null Icon id associated to service
     */
    private $iconId;

    /**
     * @var string|null
     */
    private $iconAlternativeText;

    /**
     * @var int|null
     */
    private $graphId;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return ExtendedService
     */
    public function setId(?int $id): ExtendedService
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return ExtendedService
     */
    public function setNotes(?string $notes): ExtendedService
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotesUrl(): ?string
    {
        return $this->notesUrl;
    }

    /**
     * @param string|null $notesUrl
     * @return ExtendedService
     */
    public function setNotesUrl(?string $notesUrl): ExtendedService
    {
        $this->notesUrl = $notesUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    /**
     * @param string|null $actionUrl
     * @return ExtendedService
     */
    public function setActionUrl(?string $actionUrl): ExtendedService
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIconId(): ?int
    {
        return $this->iconId;
    }

    /**
     * @param int|null $iconId
     * @return ExtendedService
     */
    public function setIconId(?int $iconId): ExtendedService
    {
        $this->iconId = $iconId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconAlternativeText(): ?string
    {
        return $this->iconAlternativeText;
    }

    /**
     * @param string|null $iconAlternativeText
     * @return ExtendedService
     */
    public function setIconAlternativeText(?string $iconAlternativeText): ExtendedService
    {
        $this->iconAlternativeText = $iconAlternativeText;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGraphId(): ?int
    {
        return $this->graphId;
    }

    /**
     * @param int|null $graphId
     * @return ExtendedService
     */
    public function setGraphId(?int $graphId): ExtendedService
    {
        $this->graphId = $graphId;
        return $this;
    }
}
