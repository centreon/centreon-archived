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

namespace CentreonPerformance\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\HumanReadable;
use CentreonPerformance\Repository\GraphView;
use Centreon\Internal\Controller;
use CentreonPerformance\Repository\Graph\Service as ServiceGraphRepository;

/**
 * Controller for display graphs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
class GraphController extends Controller
{

    /**
     * Page for search and display graph
     *
     * @method GET
     * @route /graph
     */
    public function graphAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $tmpl = $di->get('template');
        $tmpl->addJs('d3.min.js');
        $tmpl->addJs('c3.min.js');
        $tmpl->addJs('jquery.select2/select2.min.js');
        $tmpl->addJs('bootstrap-switch.min.js');
        $tmpl->addJs('moment-with-locales.js')
             ->addJs('moment-timezone-with-data.min.js');
        
        $tmpl->addJs('daterangepicker.js');
        $tmpl->addJs('hogan-3.0.0.min.js');
        $tmpl->addJs('centreon.graph.js', 'bottom', 'centreon-performance');
        $tmpl->addCss('c3.css');
        $tmpl->addCss('select2.css');
        $tmpl->addCss('select2-bootstrap.css');
        $tmpl->addCss('daterangepicker-bs3.css');
        $tmpl->addCss('bootstrap-switch.min.css');
        
        $tmpl->append(
            'jsUrl',
            array(
                'graph' => $router->getPathFor('/centreon-performance/graph')
            ),
            true
        );
        
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
        try {
            $router = Di::getDefault()->get('router');
            /* Get post information */
            $end = $router->request()->param('end_time', time());
            $start = $router->request()->param('start_time', $end - (3600 * 72));
            $serviceId = $router->request()->param('service_id');
            if (is_null($serviceId)) {
                throw new \Exception("Can't get service id");
            }

            $service = new ServiceGraphRepository($serviceId, $start, $end);

            $data = array();
            $serviceData = $service->getValues(200);
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
        } catch (\Exception $e) {
            $router->response()->code(500)->json(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }


    /**
     * Get list of service with metrics
     *
     * @route /service/withmetrics
     * @method get
     */
    public function getServiceWithMetricsAction()
    {
        $router = Di::getDefault()->get('router');
        $requestParams = $this->getParams('get');
        $finalList = array();
        
        $list = GraphView::getServiceWithMetrics($requestParams['q']);
        foreach ($list as $infos) {
            $finalList[] = array(
                'id' => $infos['service_id'],
                'text' => $infos['name'] . ' - ' . $infos['description']
            );
        }
        $router->response()->json($finalList);
    }

    /**
     * Save a graph view
     *
     * @route /view
     * @method POST
     */
    public function saveViewAction()
    {
        /* Get params */
        $di = Di::getDefault();
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
                    'success' => false,
                    'error' => $e->getMessage()
                ));
                return;
            }
        }
        try {
            GraphView::update($viewId, $listGraph);
        } catch (\Exception $e) {
            $router->response()->json(array(
                'success' => false,
                'error' => $e->getMessage()
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
     * @route /view
     * @method GET
     */
    public function getListViewAction()
    {
        $router = Di::getDefault()->get('router');

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
     * @route /view/[i:id]
     * @method GET
     */
    public function getListGraphAction()
    {
        $router = Di::getDefault()->get('router');
        $viewId = $router->request()->param('id');

        $router->response()->json(array(
            'graphs' => GraphView::getListGraph($viewId)
        ));
    }

    /**
     * Delete a graph view
     *
     * @route /view/[i:id]
     * @method DELETE
     */
    public function deleteGraphViewAction()
    {
        $router = Di::getDefault()->get('router');
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

    /**
     * Save a image to filesystem
     *
     * @route /save
     * @method POST
     */
    public function saveAsImageAction()
    {
        $router = Di::getDefault()->get('router');
        $config = Di::getDefault()->get('config');
        $svgString = $router->request()->param('svg');
        $graphId = $router->request()->param('graph_id');
        $graphType = $router->request()->param('graph_type');
        $imageName = date('Ymd-his', time()) . '_' . $graphType . '_' . $graphId . '.png';
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');

        $im = $this->graphToImage($svgString);
        if (!file_exists($centreonPath . '/www/uploads/graphs')) {
            mkdir($centreonPath . '/www/uploads/graphs');
        }
        $im->writeImage($centreonPath . '/www/uploads/graphs/' . $imageName);

        $im->clear();
        $im->destroy();

        $router->response()->json(array(
            'success' => true,
            'imagename' => $imageName
        ));
    }

    /**
     * Propose to download the graph in image
     *
     * @route /download
     * @method POST
     */
    public function downloadAction()
    {
        $router = Di::getDefault()->get('router');
        $svgString = $router->request()->param('svg');
        $graphId = $router->request()->param('graph_id');
        $graphType = $router->request()->param('graph_type');
        $imageName = date('Ymd-his', time()) . '_' . $graphType . '_' . $graphId . '.png';

        $im = $this->graphToImage($svgString);

        $router->response()->header('Content-Type', 'image/png');
        $router->response()->header('Content-Disposition', 'attachment; filename=' . $imageName);
        $router->response()->body($im->getImageBlob());
    }

    /**
     * Convert a SVG to png binaries
     *
     * @param $svgString string The svg string
     * @return Imagick The image of graph
     */
    private function graphToImage($svgString)
    {
        $config = Di::getDefault()->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        /* Construct the new string */
        $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
        /*$svg .= '<style type="text/css"><![CDATA[' . "\n";
        $svg .= file_get_contents($centreonPath . '/www/static/centreon/css/c3.css');
        $svg .= "\n]]>\n</style>\n"; */
        $svg .= $svgString;

        $im = new \Imagick();
        $im->readImageBlob($svg);
        $im->setImageFormat('png24');
        return $im;
    }
}
