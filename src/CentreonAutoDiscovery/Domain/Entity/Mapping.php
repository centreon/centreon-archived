<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
*/

namespace CentreonAutoDiscovery\Domain\Entity;

class Mapping
{
    /**
     * @var int Mapping id
     */
    private $id;

    /**
     * @var string Mapping name
     */
    private $name;

    /**
     * @var string Object name
     */
    private $object;

    /**
     * @var string Json data corresponding to the filters mapping
     */
    private $filters;

    /**
     * @var string Json data corresponding to the attributes mapping
     */
    private $attributes;

    /**
     * @var string Json data corresponding to the association mapping
     */
    private $association;

    /**
     * @var string Json data corresponding to the templates mapping
     */
    private $templates;

    /**
     * @var string Json data corresponding to the macros mapping
     */
    private $macros;

    /**
     * @return int
     * @see Mapping::$id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Mapping
     * @see Mapping::$id
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Mapping
     * @see Mapping::$name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$object
     */
    public function getObject(): string
    {
        return $this->object;
    }

    /**
     * @param string $object
     * @return Mapping
     * @see Mapping::$object
     */
    public function setObject(string $object): self
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$filters
     */
    public function getFilters(): string
    {
        return $this->filters;
    }

    /**
     * @param string $filters
     * @return Mapping
     * @see Mapping::$filters
     */
    public function setFilters(string $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$attributes
     */
    public function getAttributes(): string
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     * @return Mapping
     * @see Mapping::$attributes
     */
    public function setAttributes(string $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$association
     */
    public function getAssociation(): string
    {
        return $this->association;
    }

    /**
     * @param string $association
     * @return Mapping
     * @see Mapping::$association
     */
    public function setAssociation(string $association): self
    {
        $this->association = $association;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$templates
     */
    public function getTemplates(): string
    {
        return $this->templates;
    }

    /**
     * @param string $templates
     * @return Mapping
     * @see Mapping::$templates
     */
    public function setTemplates(string $templates): self
    {
        $this->templates = $templates;
        return $this;
    }

    /**
     * @return string
     * @see Mapping::$macros
     */
    public function getMacros(): string
    {
        return $this->macros;
    }

    /**
     * @param string $macros
     * @return Mapping
     * @see Mapping::$macros
     */
    public function setMacros(string $macros): self
    {
        $this->macros = $macros;
        return $this;
    }
}
