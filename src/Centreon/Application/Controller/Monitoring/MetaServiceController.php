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

namespace Centreon\Application\Controller\Monitoring;

use FOS\RestBundle\View\View;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric\FindMetaServiceMetrics;
use Centreon\Infrastructure\Monitoring\MetaService\API\Model\MetaServiceMetricFactoryV21;

/**
 * This class is designed to provide APIs for the context of RealTime Monitoring Servers.
 *
 * @package Centreon\Application\Controller\RealTimeMonitoringServer\Controller
 */
class MetaServiceController extends AbstractController
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindMetaServiceMetrics $findMetaServiceMetrics
     * @return View
     */
    public function findMetaServiceMetrics(
        RequestParametersInterface $requestParameters,
        FindMetaServiceMetrics $findMetaServiceMetrics,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $response = $findMetaServiceMetrics->execute($metaId);
        return $this->view(
            [
                'result' => MetaServiceMetricFactoryV21::createFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }
}
