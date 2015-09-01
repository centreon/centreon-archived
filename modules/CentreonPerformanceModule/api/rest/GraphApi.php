<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonPerformance\Api\Rest;

use Centreon\Internal\Di;
use CentreonPerformance\Repository\Graph\Service;
use Centreon\Internal\Api;
use Centreon\Internal\Exception\Http\BadRequestException;

/**
 * Controller for display graphs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphApi extends Api
{
    /**
     *
     * @route /service/[:ids]/links/graph
     * @method GET
     * @auth
     */
    public function graphServiceAction()
    {
        $headers = $this->request->headers();
        $version = null;
        if (isset($headers['centreon-version'])) {
            $version = trim($headers['centreon-version']);
        }
        $calledMethod = '\\' . get_called_class() . '::' . __FUNCTION__;
        static::executeRoute($calledMethod, $version);
    }

    /**
     *
     * @api /service/[:ids]/links/graph
     * @method GET
     * @since 3.0.0
     */
    public function graphServiceV1Action()
    {
        $router = Di::getDefault()->get('router');
        $params = $router->request()->params();

        if (false === isset($params['start']) ||
            false === is_numeric($params['start']) ||
            false === isset($params['end']) ||
            false === is_numeric($params['end'])) {
            throw new BadRequestException('Missing parameter start or end', 'You must specify a start and an end timestamp');
        }

        $start = $params['start'];
        $end = $params['end'];

        $ids = explode(',', $params['ids']);
        $result = array();
        $nbPoints = 200;
        foreach ($ids as $id) {
            $data = array();
            $service = new Service($id, $start, $end);
            $serviceData = $service->getValues($nbPoints);
            /* Parse for replace NAN */
            for ($i = 0; $i < count($serviceData); $i++) {
                if (isset($serviceData[$i]['data'])) {
                    $times = array_keys($serviceData[$i]['data']);
                    $values = array_map(function($element) {
                        if (strtoupper($element) == 'NAN') {
                            return null;
                        }
                        return $element;
                    }, array_values($serviceData[$i]['data']));
                }
                $serviceData[$i]['data'] = $values;
                $serviceData[$i]['label'] = $serviceData[$i]['legend'];
                unset($serviceData[$i]['legend']);
                $serviceData[$i]['type'] = $serviceData[$i]['graph_type'];
                unset($serviceData[$i]['graph_type']);
            }
            $result[] = array(
                'service_id' => $id,
                'data' => $serviceData,
                'times' => $times,
                'size' => $nbPoints
            );
        }
        $this->sendJsonApiResponse('graph', $result);
    }
}
