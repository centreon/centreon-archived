<?php

/*
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

namespace Centreon\Domain\Monitoring;

/**
 * Class representing a record of a note in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class Notes
{
    private ?string $label = null;

    private ?string $url = null;

    /**
     * Get label
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set notes
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get url
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set url
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }
}
