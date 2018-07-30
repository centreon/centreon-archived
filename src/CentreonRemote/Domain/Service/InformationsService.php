<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Domain\Repository\InformationsRepository;
use Curl\Curl;
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
    public function getRemoteStatus(): bool
    {
        $repository = $this->getDi()['centreon.db-manager']->getRepository(InformationsRepository::class);
        $isRemote = $repository->getOneByKey('isRemote');
        return ($isRemote->getValue() == 'yes') ? true : false;
    }

    private function getDi(): Container
    {
        return $this->di;
    }
}