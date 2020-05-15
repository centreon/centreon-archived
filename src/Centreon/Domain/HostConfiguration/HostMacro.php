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

use Centreon\Domain\Annotation\EntityDescriptor;

class HostMacro
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null Macro name
     */
    private $name;

    /**
     * @var string|null Macro value
     */
    private $value;

    /**
     * @var bool Indicates whether this macro contains a password
     * @EntityDescriptor(column="is_password", modifier="setPassword")
     */
    private $isPassword = false;

    /**
     * @var string|null Macro description
     */
    private $description;

    /**
     * @var int|null
     */
    private $order;

    /**
     * @var int|null
     */
    private $hostId;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return HostMacro
     */
    public function setId(?int $id): HostMacro
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return HostMacro
     */
    public function setName(?string $name): HostMacro
    {
        if ($name !== null) {
            if (strpos($name, '$_HOST') !== 0) {
                $name = '$_HOST' . $name;
                if ($name[-1] !== '$') {
                    $name .= '$';
                }
            }
            $this->name = strtoupper($name);
        } else {
            $this->name = null;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return HostMacro
     */
    public function setValue(?string $value): HostMacro
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPassword(): bool
    {
        return $this->isPassword;
    }

    /**
     * @param bool $isPassword
     * @return HostMacro
     */
    public function setPassword(bool $isPassword): HostMacro
    {
        $this->isPassword = $isPassword;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return HostMacro
     */
    public function setDescription(?string $description): HostMacro
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     * @return HostMacro
     */
    public function setOrder(?int $order): HostMacro
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    /**
     * @param int|null $hostId
     */
    public function setHostId(?int $hostId): void
    {
        $this->hostId = $hostId;
    }
}
