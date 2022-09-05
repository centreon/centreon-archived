<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Domain\RealTime\Model\ResourceTypes;

class ServiceResourceType extends AbstractResourceType
{
    public const TYPE_NAME = 'service',
                 TYPE_ID = 0;
    /**
     * @var string $name
     */
    protected string $name = self::TYPE_NAME;

    /**
     * @var integer $id
     */
    protected int $id = self::TYPE_ID;

    /**
     * @inheritDoc
     */
    public function hasParent(): bool
    {
        return true;
    }
}
