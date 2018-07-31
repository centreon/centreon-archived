<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Domain\Repository\InformationsRepository;
use Pimple\Container;

class InformationsService
{

    /**
     * @var Container
     */
    private $di;

    /**
     * NotifyMasterService constructor.
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Get status for centreon instance (is remote or is not remote)
     * @return bool
     */
    public function serverIsRemote(): bool
    {
        $repository = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class);
        $isRemote = $repository->getOneByKey('isRemote');

        if (!$isRemote) {
            return false;
        }

        return $isRemote->getValue() == 'yes';
    }

    /**
     * Get status for centreon instance (is master or is not master)
     * @return bool
     */
    public function serverIsMaster(): bool
    {
        return !$this->serverIsRemote();
    }

    private function getDi(): Container
    {
        return $this->di;
    }
}