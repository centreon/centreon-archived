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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\Macro\Interfaces\MacroInterface;
use Centreon\Domain\Annotation\EntityDescriptor;

class HostMacro implements MacroInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string Macro name
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        if (strpos($name, '$_HOST') !== 0) {
            $name = '$_HOST' . $name;
            if ($name[-1] !== '$') {
                $name .= '$';
            }
        }
        $this->name = strtoupper($name);
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
     * @return self
     */
    public function setValue(?string $value): self
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
     * @return self
     */
    public function setPassword(bool $isPassword): self
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
     * @return self
     */
    public function setDescription(?string $description): self
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
     * @return self
     */
    public function setOrder(?int $order): self
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
     * @return self
     */
    public function setHostId(?int $hostId): self
    {
        $this->hostId = $hostId;
        return $this;
    }
}
