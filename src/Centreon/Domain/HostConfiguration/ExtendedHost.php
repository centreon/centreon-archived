<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\HostConfiguration;

class ExtendedHost
{
    /**
     * @var int
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ExtendedHost
     */
    public function setId(int $id): ExtendedHost
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
     * @return ExtendedHost
     */
    public function setNotes(?string $notes): ExtendedHost
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
     * @return ExtendedHost
     */
    public function setNotesUrl(?string $notesUrl): ExtendedHost
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
     * @return ExtendedHost
     */
    public function setActionUrl(?string $actionUrl): ExtendedHost
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }
}
