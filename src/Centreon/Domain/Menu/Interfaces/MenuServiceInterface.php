<?php

namespace Centreon\Domain\Menu\Interfaces;

use Centreon\Domain\Menu\Model\Page;

interface MenuServiceInterface
{
    /**
     * Find a Page by its topology Page Number
     *
     * @param string $pageNumber
     * @return Page|null
     */
    public function findPageByTopologyPageNumber(string $pageNumber): ?Page;
}
