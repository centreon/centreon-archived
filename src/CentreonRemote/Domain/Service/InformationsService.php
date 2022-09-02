<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
        $repository = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(InformationsRepository::class);
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

    /**
     * Get status for centreon instance if it is a central and has connected remotes to it
     * @return bool
     */
    public function serverIsCentral(): bool
    {
        $repository = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(InformationsRepository::class);
        $isCentral = $repository->getOneByKey('isCentral');

        if (!$isCentral) {
            return false;
        }

        return $isCentral->getValue() == 'yes';
    }

    private function getDi(): Container
    {
        return $this->di;
    }
}
