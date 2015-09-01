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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonConfiguration\Repository\ScheduledDowntimeRepository;

/**
 * Configure scheduled downtime
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Controller
 * @version 3.0.0
 */
class ScheduledDowntimeController extends FormController
{
    protected $objectDisplayName = 'Scheduled Downtime';
    public static $objectName = 'scheduled-downtime';
    public static $enableDisableFieldName = 'dt_activate';
    protected $objectBaseUrl = '/centreon-configuration/scheduled-downtime';
    protected $datatableObject = '\CentreonConfiguration\Internal\ScheduledDowntimeDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\ScheduledDowntime';
    protected $repository = '\CentreonConfiguration\Repository\ScheduledDowntimeRepository';
    public static $isDisableable = true;

    public static $relationMap = array(
        'dt_hosts' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\Hosts',
        'dt_hosts_tags' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\HostsTags',
        'dt_services' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\Services',
        'dt_services_tags' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\ServicesTags'
    );


    /**
     * List of scheduled donwtime
     *
     * @method get
     * @route /scheduled-downtime
     */
    public function listAction()
    {
        $this->tpl->addCss('centreon.scheduled-downtime.css', 'centreon-configuration')
             ->addCss('bootstrap-datetimepicker.min.css')
             ->addJs('hogan-3.0.0.min.js')
             ->addJs('bootstrap-datetimepicker.min.js')
             ->addJs('centreon.scheduled-downtime.js', 'bottom', 'centreon-configuration');

        $this->tpl->addCustomJs('$(function () {
                $("#modal").on("loaded.bs.modal", function() {
                    $(".scheduled-downtime").centreonScheduledDowntime();
                });
                $("#modal").on("changed", function () {
                    $(".scheduled-downtime").centreonScheduledDowntime("resizeCal");
                });
            });');

        parent::listAction();
    }

    /**
     * Create a new period
     *
     * @method post
     * @route /scheduled-downtime/add
     */
    public function createAction()
    {
        $givenParameters = $this->getParams('post');
        $periods = json_decode($givenParameters['periods'], true);

        $id = parent::createAction(false);
        /* Update the periods */
        if (is_array($periods)) {
            ScheduledDowntimeRepository::updatePeriods($id, $periods);
        }

        $this->router->response()->json(array('success' => true));
    }

    /**
     *
     * @method get
     * @route /scheduled-downtime/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array(), $defaultValues = array())
    {
        $template = Di::getDefault()->get('template');
        $template->addCss('daterangepicker-bs3.css');
        $template->addJs('daterangepicker.js');

        parent::editAction($additionnalParamsForSmarty, $defaultValues);
    }

    /**
     * Update a period
     *
     * @method post
     * @route /scheduled-downtime/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        $periods = json_decode($givenParameters['periods'], true);

        /* Update the periods */
        if (is_array($periods)) {
            ScheduledDowntimeRepository::updatePeriods($givenParameters['object_id'], $periods);
        }

        parent::updateAction();
    }

    /**
     * Get host relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/host
     */
    public function getHostRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $hostList = ScheduledDowntimeRepository::getHostRelation($params['id']);

        $hostList = array_map(
            function ($element) {
                return array(
                    'id' => $element['host_id'],
                    'text' => $element['host_name']
                );
            },
            $hostList
        );

        $router->response()->json($hostList);
    }

    /**
     * Get host tag relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/host/tag
     */
    public function getHostTagRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $tagList = ScheduledDowntimeRepository::getHostTagRelation($params['id']);

        $tagList = array_map(
            function ($element) {
                return array(
                    'id' => $element['tag_id'],
                    'text' => $element['tagname']
                );
            },
            $tagList
        );

        $router->response()->json($tagList);
    }

    /**
     * Get service relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/service
     */
    public function getServiceRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $serviceList = ScheduledDowntimeRepository::getServiceRelation($params['id']);

        $serviceList = array_map(
            function ($element) {
                return array(
                    'id' => $element['service_id'],
                    'text' => $element['service_description']
                );
            },
            $serviceList
        );

        $router->response()->json($serviceList);
    }

    /**
     * Get service tag relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/service/tag
     */
    public function getServiceTagRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $tagList = ScheduledDowntimeRepository::getServiceTagRelation($params['id']);

        $tagList = array_map(
            function ($element) {
                return array(
                    'id' => $element['tag_id'],
                    'text' => $element['tagname']
                );
            },
            $tagList
        );

        $router->response()->json($tagList);
    }

    /**
     * Get the list of periods for a downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/period
     */
    public function getPeriodsAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');

        $periodList = ScheduledDowntimeRepository::getPeriods($params['id']);

        $router->response()->json($periodList);
    }
}
