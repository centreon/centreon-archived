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

namespace Centreon\Application\Controller;

use Centreon\Domain\Poller\Interfaces\PollerServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * This class is designed to manage all requests concerning pollers
 *
 * @package Centreon\Application\Controller
 */
class PollerController extends AbstractFOSRestController
{
    /**
     * @var PollerServiceInterface
     */
    private $pollerService;

    /**
     * PollerController constructor.
     * @param PollerServiceInterface $pollerService
     */
    public function __construct(PollerServiceInterface $pollerService)
    {
        $this->pollerService = $pollerService;
    }

    /**
     * Entry point to find the last hosts acknowledgements.
     *
     * @IsGranted("ROLE_API_CONFIGURATION", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/configuration/pollers",
     *     condition="request.attributes.get('version.is_beta') == true",
     *     name="configuration.poller.findPoller")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findPoller(RequestParametersInterface $requestParameters): View
    {
        $pollers = $this->pollerService->findPollers();
        $context = (new Context())->setGroups(['poller_main']);

        return $this->view([
            'result' => $pollers,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }
}
