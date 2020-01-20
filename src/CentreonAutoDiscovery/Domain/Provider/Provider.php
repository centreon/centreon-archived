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
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Domain\Provider;

use CentreonAutoDiscovery\Domain\Parameter\Parameter;

class Provider
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var mixed|null Source of icon in Base64 format
     */
    private $icon;

    /**
     * @var int|null Command id linked to the provider
     */
    private $commandId;

    /**
     * @var int|null Host id that will be used as a template to create the new host
     */
    private $defaultTemplateId;

    /**
     * @var string Name of the provider type
     */
    private $providerType;

    /**
     * @Centreon\Domain\Annotation\EntityDescriptor(column="parameters", modifier="setParametersFromJson")
     * @var Parameter[] Parameters that will be used to connect with the provider
     */
    private $parameters;

    /**
     * @Centreon\Domain\Annotation\EntityDescriptor(column="attributes", modifier="setAttributesFromJson")
     * @var array<string, array<string, string>> Contains the definitions of the attributes of the discovery result
     */
    private $attributes;

    /**
     * @Centreon\Domain\Annotation\EntityDescriptor(column="need_proxy", modifier="setNeedProxy")
     * @JMS\Serializer\Annotation\SerializedName("need_proxy")
     * @var bool Indicates whether the connection to the provider should use a proxy configuration
     */
    private $hasNeedProxy = false;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Provider
     */
    public function setId(?int $id): self
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
     * @return Provider
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
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
     * @return Provider
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed|null $icon
     * @return Provider
     */
    public function setIcon($icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCommandId (): ?int
    {
        return $this->commandId;
    }

    /**
     * @param int|null $commandId
     * @return Provider
     */
    public function setCommandId (?int $commandId): self
    {
        $this->commandId = $commandId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDefaultTemplateId (): ?int
    {
        return $this->defaultTemplateId;
    }

    /**
     * @param int|null $defaultTemplateId
     * @return Provider
     */
    public function setDefaultTemplateId (?int $defaultTemplateId): self
    {
        $this->defaultTemplateId = $defaultTemplateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getProviderType (): string
    {
        return $this->providerType;
    }

    /**
     * @param string $providerType
     * @return Provider
     */
    public function setProviderType (string $providerType): Provider
    {
        $this->providerType = $providerType;
        return $this;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters (): array
    {
        return $this->parameters;
    }

    /**
     * @param Parameter[] $parameters
     * @return Provider
     */
    public function setParameters (array $parameters): Provider
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param string $parameters Parameters in JSON format
     * @return $this
     */
    public function setParametersFromJson(string $parameters): self
    {
        $this->parameters = json_decode($parameters, true) ?? [];
        return $this;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAttributes (): array
    {
        return $this->attributes;
    }

    /**
     * @param array<string, array<string, string>> $attributes
     * @return Provider
     */
    public function setAttributes (array $attributes): Provider
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $attributes Attributes in JSON format
     * @return $this
     */
    public function setAttributesFromJson(string $attributes): self
    {
        $this->attributes = json_decode($attributes, true) ?? [];
        return $this;
    }

    /**
     * @return bool
     */
    public function hasNeedProxy (): bool
    {
        return $this->hasNeedProxy;
    }

    /**
     * @param bool $hasNeedProxy
     * @return Provider
     */
    public function setNeedProxy (bool $hasNeedProxy): Provider
    {
        $this->hasNeedProxy = $hasNeedProxy;
        return $this;
    }
}
