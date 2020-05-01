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

namespace Centreon\Domain\Proxy;

use Centreon\Domain\Proxy\Interfaces\ProxyRepositoryInterface;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;

/**
 * This class is designed to manage proxy-related actions such as configuration.
 *
 * @package Centreon\Domain\Proxy
 */
class ProxyService implements ProxyServiceInterface
{
    /**
     * @var ProxyRepositoryInterface
     */
    private $proxyRepository;

    /**
     * ProxyService constructor.
     *
     * @param ProxyRepositoryInterface $proxyRepository
     */
    public function __construct(ProxyRepositoryInterface $proxyRepository)
    {
        $this->proxyRepository = $proxyRepository;
    }

    /**
     * @inheritDoc
     */
    public function getProxy(): Proxy
    {
        return $this->proxyRepository->getProxy();
    }

    /**
     * @inheritDoc
     */
    public function updateProxy(Proxy $proxy): void
    {
        $this->proxyRepository->updateProxy($proxy);
    }
}
