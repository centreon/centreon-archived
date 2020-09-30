<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class is design to manage REST API requests concerning the platform's information used in the configuration.
 *
 * @package Centreon\Application\Controller\Configuration
 */
class InformationController extends AbstractController
{
    /**
     * @var PlatformInformation
     */
    private $informationService;

    public function __construct(PlatformInformationServiceInterface $informationService)
    {
        $this->informationService = $informationService;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function getInformation(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        if (!$this->getUser()->isAdmin()) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }
        return $this->view($this->informationService->getInformation());
    }
}