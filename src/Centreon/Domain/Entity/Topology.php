<?php

namespace Centreon\Domain\Entity;

class Topology
{
    const ENTITY_IDENTIFICATOR_COLUMN = 'topology_id';
    const TABLE = 'topology';

    /**
     * @var int
     */
    protected $topology_id;

    /**
     * @var string
     */
    protected $topology_name;

    /**
     * @var int
     */
    protected $topology_parent;

    /**
     * @var string
     */
    protected $topology_page;

    /**
     * @var int
     */
    protected $topology_order;

    /**
     * @var int
     */
    protected $topology_group;

    /**
     * @var string
     */
    protected $topology_url;

    /**
     * @var string
     */
    protected $topology_url_opt;

    /**
     * @var string
     * enum('0','1')
     */
    protected $topology_popup;

    /**
     * @var string
     * enum('0','1')
     */
    protected $topology_modules;

    /**
     * @var string
     * enum('0','1')
     */
    protected $topology_show;

    /**
     * @var string
     */
    protected $topology_style_class;

    /**
     * @var string
     */
    protected $topology_style_id;

    /**
     * @var string
     */
    protected $topology_OnClick;


    /**
     * @var string
     * enum('0','1')
     */
    protected $readonly;

    /**
     * @var string
     * enum('0','1')
     */
    protected $is_react;

    /**
     * @return int
     */
    public function getTopologyId(): int
    {
        return $this->topology_id;
    }

    /**
     * @param int $topology_id
     */
    public function setTopologyId(int $topology_id): void
    {
        $this->topology_id = $topology_id;
    }

    /**
     * @return string
     */
    public function getTopologyName(): ?string
    {
        return $this->topology_name;
    }

    /**
     * @param string $topology_name
     */
    public function setTopologyName(?string $topology_name): void
    {
        $this->topology_name = $topology_name;
    }

    /**
     * @return int
     */
    public function getTopologyParent(): ?int
    {
        return $this->topology_parent;
    }

    /**
     * @param int $topology_parent
     */
    public function setTopologyParent(?int $topology_parent): void
    {
        $this->topology_parent = $topology_parent;
    }

    /**
     * @return string
     */
    public function getTopologyPage(): ?string
    {
        return $this->topology_page;
    }

    /**
     * @param string $topology_page
     */
    public function setTopologyPage(?string $topology_page): void
    {
        $this->topology_page = $topology_page;
    }

    /**
     * @return int
     */
    public function getTopologyOrder(): ?int
    {
        return $this->topology_order;
    }

    /**
     * @param int $topology_order
     */
    public function setTopologyOrder(?int $topology_order): void
    {
        $this->topology_order = $topology_order;
    }

    /**
     * @return int
     */
    public function getTopologyGroup(): ?int
    {
        return $this->topology_group;
    }

    /**
     * @param int $topology_group
     */
    public function setTopologyGroup(?int $topology_group): void
    {
        $this->topology_group = $topology_group;
    }

    /**
     * @return string
     */
    public function getTopologyUrl(): ?string
    {
        return $this->topology_url;
    }

    /**
     * @param string $topology_url
     */
    public function setTopologyUrl(?string $topology_url): void
    {
        $this->topology_url = $topology_url;
    }

    /**
     * @return string
     */
    public function getTopologyUrlOpt(): ?string
    {
        return $this->topology_url_opt;
    }

    /**
     * @param string $topology_url_opt
     */
    public function setTopologyUrlOpt(?string $topology_url_opt): void
    {
        $this->topology_url_opt = $topology_url_opt;
    }

    /**
     * @return string
     */
    public function getTopologyPopup(): ?string
    {
        return $this->topology_popup;
    }

    /**
     * @param string $topology_popup
     */
    public function setTopologyPopup(?string $topology_popup): void
    {
        $this->topology_popup = $topology_popup;
    }

    /**
     * @return string
     */
    public function getTopologyModules(): ?string
    {
        return $this->topology_modules;
    }

    /**
     * @param string $topology_modules
     */
    public function setTopologyModules(?string $topology_modules): void
    {
        $this->topology_modules = $topology_modules;
    }

    /**
     * @return string
     */
    public function getTopologyShow(): ?string
    {
        return $this->topology_show;
    }

    /**
     * @param string $topology_show
     */
    public function setTopologyShow(?string $topology_show): void
    {
        $this->topology_show = $topology_show;
    }

    /**
     * @return string
     */
    public function getTopologyStyleClass(): ?string
    {
        return $this->topology_style_class;
    }

    /**
     * @param string $topology_style_class
     */
    public function setTopologyStyleClass(?string $topology_style_class): void
    {
        $this->topology_style_class = $topology_style_class;
    }

    /**
     * @return string
     */
    public function getTopologyStyleId(): ?string
    {
        return $this->topology_style_id;
    }

    /**
     * @param string $topology_style_id
     */
    public function setTopologyStyleId(?string $topology_style_id): void
    {
        $this->topology_style_id = $topology_style_id;
    }

    /**
     * @return string
     */
    public function getReadonly(): string
    {
        return $this->readonly;
    }

    /**
     * @param string $readonly
     */
    public function setReadonly(string $readonly): void
    {
        $this->readonly = $readonly;
    }

    /**
     * @return string
     */
    public function getIsReact(): string
    {
        return $this->is_react;
    }

    /**
     * @param string $is_react
     */
    public function setIsReact(string $is_react): void
    {
        $this->is_react = $is_react;
    }

    /**
     * @return string
     */
    public function getTopologyOnClick(): ?string
    {
        return $this->topology_OnClick;
    }

    /**
     * @param string $topology_OnClick
     */
    public function setTopologyOnClick(?string $topology_OnClick): void
    {
        $this->topology_OnClick = $topology_OnClick;
    }
}
