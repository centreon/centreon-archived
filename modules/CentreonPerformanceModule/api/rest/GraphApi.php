<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
