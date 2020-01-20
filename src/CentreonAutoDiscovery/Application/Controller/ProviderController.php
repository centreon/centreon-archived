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
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Application\Controller;

use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use CentreonAutoDiscovery\Domain\Provider\Interfaces\ProviderServiceInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProviderController extends AbstractFOSRestController
{

    /**
     * @var ProviderServiceInterface
     */
    private $providerService;

    public function __construct(ProviderServiceInterface $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/providers",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="auto-discovery.findProviders")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findProviders(RequestParametersInterface $requestParameters): View
    {
        $providers = $this->providerService->findProviders();

        return $this->view([
            'result' => $providers,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext((new Context())->setGroups(['Default']));
    }
}
