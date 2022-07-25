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

namespace Core\Tag\RealTime\Domain\Model;

use Centreon\Domain\Common\Assertion\Assertion;

class Tag
{
    public const SERVICE_GROUP_TYPE_ID = 0,
        HOST_GROUP_TYPE_ID = 1,
        SERVICE_CATEGORY_TYPE_ID = 2,
        HOST_CATEGORY_TYPE_ID = 3;

    private int $type;

    /**
     * @param int $id
     * @param string $name
     * @param int $type
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(private int $id, private string $name, int $type)
    {
        Assertion::notEmpty($name, 'Tag::name');
        $this->name = $name;
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setter for $type property
     *
     * @param int $type
     * @return Tag
     * @throws \InvalidArgumentException
     */
    public function setType(int $type): Tag
    {
        if (! in_array($type, self::getAvailableTypeIds())) {
            throw new \InvalidArgumentException('Type Id is not valid');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Retrieves the list of available type ids
     *
     * @return int[]
     */
    private static function getAvailableTypeIds(): array
    {
        return [
            self::SERVICE_GROUP_TYPE_ID,
            self::HOST_GROUP_TYPE_ID,
            self::SERVICE_CATEGORY_TYPE_ID,
            self::HOST_CATEGORY_TYPE_ID,
        ];
    }
}
