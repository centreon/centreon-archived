<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Application\Controller\Configuration;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroups;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup\HostGroupV2110Factory;
use FOS\RestBundle\View\View;

/**
 * This class is designed to provide APIs for the context of host group.
 *
 * @package Centreon\Application\Controller
 */
class HostGroupController extends AbstractController
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindHostGroups $findHostGroup
     * @return View
     * @throws \Assert\AssertionFailedException
     * @throws \Centreon\Domain\HostConfiguration\Exception\HostGroupException
     */
    public function findHostGroups(RequestParametersInterface $requestParameters, FindHostGroups $findHostGroup): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $findHostGroup->execute();
        return $this->view(
            [
                'result' => HostGroupV2110Factory::createFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }
}
