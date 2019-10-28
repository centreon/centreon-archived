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

namespace Centreon\Infrastructure\Downtime;

use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Use to manage all downtime requests.
 *
 * @package Centreon\Application\Controller
 */
class RestApiDowntime extends AbstractFOSRestController
{

    /**
     * @var DowntimeServiceInterface
     */
    private $downtimeService;

    public function __construct(DowntimeServiceInterface $downtimeService)
    {
        $this->downtimeService = $downtimeService;
    }

    /**
     * Entry point to find the last hosts acknowledgements.
     *
     * @IsGranted("ROLE_API_REALTIME", message="You are not authorized to access this resource")
     * @Rest\Get(
     *     "/downtimes/hosts",
     *     condition="request.attributes.get('version.is_beta') == true")
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findHostDowntime(RequestParametersInterface $requestParameters): View
    {
        $hostsDowntime = $this->downtimeService
            ->filterByContact($this->getUser())
            ->findHostDowntime();

        $context = (new Context())->setGroups(['dwt_main']);

        return $this->view([
            'result' => $hostsDowntime,
            'meta' => [
                'pagination' => $requestParameters->toArray()
            ]
        ])->setContext($context);
    }
}
