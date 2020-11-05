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

/**
 * Resource Links Uris model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceExternalLinks
{
    /**
     * @var string|null
     */
    private $action_url;

    /**
     * @var string|null
     */
    private $notes_url;

    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->action_url;
    }

    /**
     * @param string|null $action_url
     * @return self
     */
    public function setActionUrl(?string $action_url): self
    {
        $this->action_url = $action_url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotesUrl(): ?string
    {
        return $this->notes_url;
    }

    /**
     * @param string|null $notes_url
     * @return self
     */
    public function setNotesUrl(?string $notes_url): self
    {
        $this->notes_url = $notes_url;

        return $this;
    }
}
