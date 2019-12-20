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
 *
 */
declare(strict_types=1);

namespace Centreon\Infrastructure\Proxy;

use Centreon\Domain\Proxy\Interfaces\ProxyRepositoryInterface;
use Centreon\Domain\Proxy\Proxy;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

class ProxyRepositoryRDB extends AbstractRepositoryDRB implements ProxyRepositoryInterface
{
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function updateProxy(Proxy $proxy): void
    {
        $request =
            'DELETE FROM `:db`.options 
            WHERE `key` IN (\'proxy_url\', \'proxy_port\', \'proxy_user\', \'proxy_password\')';

        $request = $this->translateDbName($request);

        $this->db->query($request);

        $request = 'INSERT INTO `:db`.options (`key`,`value`) VALUES (:key, :value)';
        $request = $this->translateDbName($request);
        $prepareStatement = $this->db->prepare($request);

        $data = [
            'proxy_url' => $proxy->getUrl(),
            'proxy_port' => $proxy->getPort(),
            'proxy_user' => $proxy->getUser(),
            'proxy_password' => $proxy->getPassword()
        ];

        foreach ($data as $key => $value) {
            $prepareStatement->bindParam(':key', $key);
            $prepareStatement->bindParam(':value', $value);
            $prepareStatement->execute();
        }
    }

    /**
     * @inheritDoc
     */
    public function getProxy(): Proxy
    {
        $request = $this->translateDbName(
            'SELECT * FROM `:db`.options WHERE `key` LIKE \'proxy_%\''
        );
        $proxy = new Proxy();
        $statement = $this->db->query($request);
        if ($statement !== false) {
            $proxyDetails = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
            if (!empty($proxyDetails)) {
                $proxy->setUrl($proxyDetails['proxy_url'] ?? null);
                $proxy->setPort(((int) $proxyDetails['proxy_port']) ?? null);
                $proxy->setUser($proxyDetails['proxy_user'] ?? null);
                $proxy->setPassword($proxyDetails['proxy_password'] ?? null);
            }
        }
        return $proxy;
    }
}
