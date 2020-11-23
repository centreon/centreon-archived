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

namespace Centreon\Domain\HostConfiguration\Model;

/**
 * This class is designed to represent a geographical coordinate of the host.
 *
 * @package Centreon\Domain\HostConfiguration\Model
 */
class GeographicalCoordinates
{
    /**
     * @var string
     */
    private $longitude;

    /**
     * @var string
     */
    private $latitude;

    /**
     * @param string $latitude
     * @param string $longitude
     */
    public function __construct(string $latitude, string $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return GeographicalCoordinates
     */
    public function setLongitude(string $longitude): GeographicalCoordinates
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return GeographicalCoordinates
     */
    public function setLatitude(string $latitude): GeographicalCoordinates
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string Formats to latitude,longitude<br/>
     * ex: 48.8534,2.3488
     */
    public function __toString()
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return sprintf('%s,%s', $this->latitude, $this->longitude);
        }
        return '';
    }
}
