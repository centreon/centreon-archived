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

    private const DEFAULT_DOWNTIME_DURATION = 'monitoring_dwt_duration',
                  DEFAULT_DOWNTIME_DURATION_SCALE = 'monitoring_dwt_duration_scale',
                  DEFAULT_REFRESH_INTERVAL = 'AjaxTimeReloadMonitoring';

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
        $downtimeDuration = '';
        $downtimeScale = '';
        $refreshInterval = '';

        $options = $this->optionService->findSelectedOptions([
            self::DEFAULT_DOWNTIME_DURATION,
            self::DEFAULT_DOWNTIME_DURATION_SCALE,
            self::DEFAULT_REFRESH_INTERVAL
        ]);

        foreach ($options as $option) {
            switch ($option->getName()) {
                case self::DEFAULT_DOWNTIME_DURATION:
                    $downtimeDuration = $option->getValue();
                    break;
                case self::DEFAULT_DOWNTIME_DURATION_SCALE:
                    $downtimeScale = $option->getValue();
                    break;
                case self::DEFAULT_REFRESH_INTERVAL:
                    $refreshInterval = $option->getValue();
                    break;
                default:
                    break;
            }
        }

        $parameters[self::KEY_NAME_CONCORDANCE[self::DEFAULT_DOWNTIME_DURATION]] =
            $this->convertToSeconds((int) $downtimeDuration, $downtimeScale);

        $parameters[self::KEY_NAME_CONCORDANCE[self::DEFAULT_REFRESH_INTERVAL]] = (int) $refreshInterval;

        return $this->view($parameters);
    }

    /**
     * Converts the combination stored in DB into seconds
     *
     * @param integer $duration
     * @param string $scale
     * @return integer
     */
    private function convertToSeconds(int $duration, string $scale): int
    {
        switch ($scale) {
            case 'm':
                return ($duration * 60);
            case 'h':
                return ($duration * 3600);
            case 'd':
                return ($duration * 86400);
            default:
                return $duration;
        }
    }
}
