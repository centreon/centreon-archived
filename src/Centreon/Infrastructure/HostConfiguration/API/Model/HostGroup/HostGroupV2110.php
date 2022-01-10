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

namespace Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup;

/**
 * This class is designed to represent the formatted response of the API request.
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup
 */
class HostGroupV2110
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $alias;

    /**
     * @var string|null
     */
    public $notes;

    /**
     * @var string|null
     */
    public $notesUrl;

    /**
     * @var string|null
     */
    public $actionUrl;

    /**
     * @var string|null
     */
    public $icon;

    /**
     * @var string|null
     */
    public $iconMap;

    /**
     * @var int|null
     */
    public $rrd;

    /**
     * @var string|null
     */
    public $geoCoords;

    /**
     * @var string|null
     */
    public $comment;

    /**
     * @var bool
     */
    public $isActivated;
}
