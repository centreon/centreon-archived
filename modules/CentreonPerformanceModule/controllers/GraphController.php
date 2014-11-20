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

namespace CentreonPerformance\Controllers;

use Centreon\Internal\Utils\HumanReadable;
use CentreonPerformance\Repository\GraphView;

/**
 * Controller for display graphs
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphController extends \Centreon\Internal\Controller
{

    /**
     * Page for search and display graph
     *
     * @method GET
     * @route /graph
     */
    public function graphAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $tmpl = $di->get('template');
        $tmpl->addJs('d3.min.js');
        $tmpl->addJs('c3.min.js');
        $tmpl->addJs('jquery.select2/select2.min.js');
        $tmpl->addJs('moment-with-langs.min.js');
        $tmpl->addJs('daterangepicker.js');
        $tmpl->addJs('centreon.graph.js', 'bottom', 'centreon-performance');
        $tmpl->addCss('c3.css');
        $tmpl->addCss('select2.css');
        $tmpl->addCss('select2-bootstrap.css');
        $tmpl->addCss('daterangepicker-bs3.css');
        $tmpl->display('file:[CentreonPerformanceModule]graph.tpl');
    }

    /**
     * Get the data for graph
     *
     * @method POST
     * @route /graph
     */
    public function graphDataAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        /* Get post information */
        $end = $router->request()->param('end_time', time());
        $start = $router->request()->param('start_time', $end - (3600 * 72));
        $serviceId = $router->request()->param('service_id');
        if (is_null($serviceId)) {
            // @todo Error http
        }

        $service = new \CentreonPerformance\Repository\Graph\Service($serviceId, $start, $end);

        $data = array();
        $serviceData = $service->getValues();
        /* Get times and convert for javascript */
        $data['times'] = array_keys($serviceData[0]['data']);
        $data['times'] = array_map(function($time) {
            return $time * 1000;
        }, $data['times']);
        $data['metrics'] = array();
        /* Check unit for human readable */
        $units = array();
        foreach ($serviceData as $metric) {
            if (false === isset($units[$metric['unit']])) {
                $units[$metric['unit']] = 0;
            }
            $values = array_map(function($value) {
                if (strval($value) == "NAN") {
                    return 0;
                }
                return $value;
            }, $metric['data']);
            $factor = HumanReadable::getFactor($values);
            if ($units[$metric['unit']] < $factor) {
                $units[$metric['unit']] = $factor;
            }
        }

        /* Convert data for c3js */
        foreach ($serviceData as $metric) {
            $metric['data'] = array_values($metric['data']);
            $metric['data'] = array_map(function($data) {
                if (strval($data) == "NAN") {
                    return null;
                }
                return $data;
            }, $metric['data']);
            $metric['data'] = HumanReadable::convertArrayWithFactor($metric['data'], $metric['unit'], $units[$metric['unit']], 3);
            if (in_array($metric['unit'], array_keys(HumanReadable::$units))) {
                $metric['unit'] = HumanReadable::$units[$metric['unit']]['units'][$units[$metric['unit']]];
            }
            $data['metrics'][] = $metric;
        }

        $router->response()->json($data);
    }

    /**
     * Save a graph view
     *
     * @route /graph/view
     * @method POST
     */
    public function saveViewAction()
    {
        /* Get params */
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');

        $listGraph = $router->request()->param('graphs');
        $viewId = $router->request()->param('viewId');
        if ("" === $viewId) {
            $viewName = $router->request()->param('viewName');
            $viewPrivacy = $router->request()->param('viewPrivacy');
            try {
                $viewId = GraphView::add($viewName, $viewPrivacy);
            } catch (\Exception $e) {
                $router->response()->json(array(
                    'success' => false
                ));
                return;
            }
        }
        try {
            GraphView::update($viewId, $listGraph);
        } catch (\Exception $e) {
            $router->response()->json(array(
                'success' => false
            ));
            return;
        }
        $router->response()->json(array(
            'success' => true
        ));
    }

    /**
     * Save a graph view
     *
     * @route /graph/view
     * @method GET
     */
    public function getListViewAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');

        $list = GraphView::getList();
        $response = array();
        foreach ($list as $id => $text) {
            $response[] = array(
                'id' => $id,
                'text' => $text
            );
        }
        $router->response()->json($response);
    }

    /**
     * Load the list of graph for a view
     *
     * @route /graph/view/[i:id]
     * @method GET
     */
    public function getListGraphAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $viewId = $router->request()->param('id');

        $router->response()->json(array(
            'graphs' => GraphView::getListGraph($viewId)
        ));
    }

    /**
     * Delete a graph view
     *
     * @route /graph/view/[i:id]
     * @method DELETE
     */
    public function deleteGraphViewAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $viewId = $router->request()->param('id');

        try {
            GraphView::delete($viewId);
        } catch (\Exception $e) {
            $router->response()->json(array(
                'success' => false
            ));
            return;
        }
        $router->response()->json(array(
            'success' => true
        ));
    }
}
