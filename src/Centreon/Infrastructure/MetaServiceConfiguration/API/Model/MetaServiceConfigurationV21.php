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

namespace Centreon\Infrastructure\MetaServiceConfiguration\API\Model;

/**
 * This class is designed to represent the formatted response of the API request.
 *
 * @package Centreon\Infrastructure\MetaServiceConfiguration\API\Model
 */
class MetaServiceConfigurationV21
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $isActivated;

    /**
     * @var string
     */
    public $output;

    /**
     * @var string
     */
    public $calculationType;

    /**
     * @var int
     */
    public $dataSourceType;

    /**
     * @var int
     */
    public $metaSelectMode;

    /**
     * @var string
     */
    public $regexpString;

    /**
     * @var string
     */
    public $metric;

    /**
     * @var string
     */
    public $warning;

    /**
     * @var string
     */
    public $critical;
}
