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

namespace Centreon\Application\Controller\Administration;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use FOS\RestBundle\View\View;

/**
 * Used to get global parameters
 *
 * @package Centreon\Application\Controller
 */
class ParametersController extends AbstractController
{
    /**
     * @var OptionServiceInterface
     */
    private $optionService;

    private const DEFAULT_DOWNTIME_DURATION = 'monitoring_dwt_duration';
    private const DEFAULT_REFRESH_INTERVAL = 'AjaxTimeReloadMonitoring';

    /**
     * Needed to make response "more readable"
     */
    private const KEY_NAME_CONCORDANCE = [
        self::DEFAULT_REFRESH_INTERVAL => 'monitoring_default_refresh_interval',
        self::DEFAULT_DOWNTIME_DURATION => 'monitoring_default_downtime_duration'
    ];

    /**
     * Parameters constructor.
     *
     * @param OptionServiceInterface $optionService
     */
    public function __construct(OptionServiceInterface $optionService)
    {
        $this->optionService = $optionService;
    }

    /**
     * Entry point to get global parameters stored in options table
     *
     * @return View
     */
    public function getParameters(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $parameters = [];

        $options = $this->optionService->findSelectedOptions([
            self::DEFAULT_DOWNTIME_DURATION,
            self::DEFAULT_REFRESH_INTERVAL
        ]);

        foreach ($options as $option) {
            $parameters[self::KEY_NAME_CONCORDANCE[$option->getName()]] = $option->getValue();
        }

        return $this->view($parameters);
    }
}
