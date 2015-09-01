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
use Centreon\Controllers\FormController;
use CentreonPerformance\Repository\GraphView;
use CentreonPerformance\Repository\GraphTemplate as GraphTemplateRepository;

/**
 * Controller for config template graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class ConfigGraphTemplateController extends FormController
{
    public static $moduleName = 'CentreonPerformance';
    public static $objectName = 'graphtemplate';
    public static $moduleShortName = 'centreon-performance';
    protected static $relationMap = array();
    protected $objectClass = '\CentreonPerformance\Models\GraphTemplate';
    protected $objectDisplayName = 'GraphTemplate';
    protected $datatableObject = '\CentreonPerformance\Internal\GraphTemplateDatatable';
    protected $repository = '\CentreonPerformance\Repository\GraphTemplate';
    protected $objectBaseUrl = '/centreon-performance/configuration/graphtemplate';

    /**
     *
     * @var type
     */
    public static $displaySearchBar = true;

    /**
     *
     * @method get
     * @route /configuration/graphtemplate
     */
    public function listAction()
    {
        $this->tpl->addCss('spectrum.css');
        $this->tpl->addJs('spectrum.js')
                  ->addJs('component/customcurvegraph.js');

        $this->tpl->addCustomJs('$(function () {
                $("#modal").on("loaded.bs.modal", function() {
                    initCustomCurveGraph();
                });
            });');

        parent::listAction();
    }

    /**
     *
     * @method get
     * @route /configuration/graphtemplate/add
     */
    public function addAction()
    {
        parent::addAction();
    }

    /**
     *
     * @method post
     * @route /configuration/graphtemplate/add
     */
    public function createAction()
    {
        $id = parent::createAction(false);

        $givenParameters = clone $this->getParams('post');

        $listMetrics = array();
        if (isset($givenParameters['metric_name'])) {
            foreach ($givenParameters['metric_name'] as $key => $value) {
                if (!empty($value)) {
                    $listMetrics[$key]['metric_name'] = $value;
                    if (isset($givenParameters['metric_fill'][$key])) {
                        $listMetrics[$key]['metric_fill'] = '1';
                    } else {
                        $listMetrics[$key]['metric_fill'] = '0';
                    }
                    if (isset($givenParameters['metric_negative'][$key])) {
                        $listMetrics[$key]['metric_negative'] = '1';
                    } else {
                        $listMetrics[$key]['metric_negative'] = '0';
                    }
                    if (isset($givenParameters['metric_color'][$key])) {
                        $listMetrics[$key]['metric_color'] = $givenParameters['metric_color'][$key];
                    } else {
                        $listMetrics[$key]['metric_color'] = '#000000';
                    }
                }
            }
        }

        try{
            GraphTemplateRepository::saveMetrics($id, 'add', $listMetrics);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $errorMessage));
        }

        $this->router->response()->json(array('success' => true));
    }

    /**
     *
     * @method post
     * @route /configuration/graphtemplate/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * @method get
     * @route /configuration/graphtemplate/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }


    /**
     * @method get
     * @route /configuration/graphtemplate/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array())
    {
        parent::editAction($additionnalParamsForSmarty);
    }

    /**
     * Update the graph template
     *
     * @method post
     * @route /configuration/graphtemplate/update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');

        $listMetrics = array();
        if (isset($givenParameters['metric_name'])) {
            foreach ($givenParameters['metric_name'] as $key => $value) {
                if (!empty($value)) {
                    $listMetrics[$key]['metric_name'] = $value;
                    if (isset($givenParameters['metric_fill'][$key])) {
                        $listMetrics[$key]['metric_fill'] = '1';
                    } else {
                        $listMetrics[$key]['metric_fill'] = '0';
                    }
                    if (isset($givenParameters['metric_negative'][$key])) {
                        $listMetrics[$key]['metric_negative'] = '1';
                    } else {
                        $listMetrics[$key]['metric_negative'] = '0';
                    }
                    if (isset($givenParameters['metric_color'][$key])) {
                        $listMetrics[$key]['metric_color'] = $givenParameters['metric_color'][$key];
                    } else {
                        $listMetrics[$key]['metric_color'] = '#000000';
                    }
                }
            }
        }

        try{
            GraphTemplateRepository::saveMetrics($givenParameters['object_id'], 'update', $listMetrics);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $errorMessage));
        }

        parent::updateAction();
    }

    /**
     * Get the list of metrics name for a service template
     *
     * @method POST
     * @route /configuration/graphtemplate/listMetrics
     */
    public function getListMetricsAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $svcTmplId = $router->request()->param('svc_tmpl_id', 0);

        $metrics = GraphView::getMetricsNameByServiceTemplate($svcTmplId);
        $router->response()->json(array(
            'success' => true,
            'data' => $metrics
        ));
    }

    /**
     * Get the service template for a graph template
     *
     * @method get
     * @route /configuration/graphtemplate/[i:id]/servicetemplate
     */
    public function getServiceTemplateAction()
    {
        parent::getSimpleRelation('svc_tmpl_id', '\CentreonConfiguration\Models\Servicetemplate');
    }

    /**
     * Get the list of service template without graph template
     *
     * @method get
     * @route /configuration/servicetemplate/withoutgraphtemplate
     */
    public function getServiceTemplateWithoutGraphtemplateAction()
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $router = Di::getDefault()->get('router');
        $query = "SELECT service_id, service_description
            FROM cfg_services
            WHERE service_register = '0'
                AND service_id NOT IN (SELECT svc_tmpl_id FROM cfg_graph_template)";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $list = array();
        while ($row = $stmt->fetch()) {
            $list[] = array(
                'id' => $row['service_id'],
                'text' => $row['service_description']
            );
        }
        $router->response()->json($list);
    }
}
