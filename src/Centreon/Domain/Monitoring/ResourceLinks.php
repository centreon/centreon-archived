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

use Centreon\Domain\Monitoring\ResourceLinksUris as Uris;
use Centreon\Domain\Monitoring\ResourceLinksEndpoints as Endpoints;
use Centreon\Domain\Monitoring\ResourceExternalLinks as Externals;

/**
 * Resource Links model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceLinks
{
    /**
     * @var Uris
     */
    private $uris;

    /**
     * @var Endpoints
     */
    private $endpoints;

    /**
     * @var Externals
     */
    private $externals;

    /**
     * ResourceLinks constructor.
     */
    public function __construct()
    {
        $this->uris = new Uris();
        $this->endpoints = new Endpoints();
        $this->externals = new Externals();
    }

    /**
     * @return Uris
     */
    public function getUris(): Uris
    {
        return $this->uris;
    }

    /**
     * @param Uris $uris
     * @return self
     */
    public function setUris(Uris $uris): self
    {
        $this->uris = $uris;

        return $this;
    }

    /**
     * @return Endpoints
     */
    public function getEndpoints(): Endpoints
    {
        return $this->endpoints;
    }

    /**
     * @param Endpoints $endpoints
     * @return self
     */
    public function setEndpoints(Endpoints $endpoints): self
    {
        $this->endpoints = $endpoints;

        return $this;
    }

    /**
     * @param Externals $externals
     * @return self
     */
    public function setExternals(Externals $externals): self
    {
        $this->externals = $externals;

        return $this;
    }

    /**
     * @return Externals
     */
    public function getExternals(): Externals
    {
        return $this->externals;
    }
}
