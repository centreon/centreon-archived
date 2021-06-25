<?php

namespace Centreon\Domain\Menu;

use Centreon\Domain\Menu\Interfaces\MenuRepositoryInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Centreon\Domain\Menu\Model\Page;

class MenuService implements MenuServiceInterface
{
    /**
     * @var MenuRepositoryInterface
     */
    private $menuRepository;

    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    /**
     * @inheritDoc
     */
    public function findPageByTopologyPageNumber(string $pageNumber): ?Page
    {
        return $this->menuRepository->findPageByTopologyPageNumber($pageNumber);
    }
}
