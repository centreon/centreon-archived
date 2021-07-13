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

namespace Centreon\Domain\Menu\Model;

class Page
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string|null
     */
    private $urlOptions;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var bool
     */
    private $isReact;

    public function __construct(int $id, string $url, int $pageNumber, bool $isReact = false)
    {
        $this->id = $id;
        $this->url = $url;
        $this->pageNumber = $pageNumber;
        $this->isReact = $isReact;
    }

    /**
     * @return int|null
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getUrlOptions(): ?string
    {
        return $this->urlOptions;
    }

    /**
     * @param string|null $urlOptions
     * @return self
     */
    public function setUrlOptions(?string $urlOptions): self
    {
        $this->urlOptions = $urlOptions;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * @return boolean
     */
    public function isReact(): bool
    {
        return $this->isReact;
    }
}
