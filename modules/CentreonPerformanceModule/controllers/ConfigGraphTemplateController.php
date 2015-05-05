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

use Centreon\Internal\Di;
use Centreon\Internal\Utils\HumanReadable;
use Centreon\Controllers\FormController;
use CentreonPerformance\Repository\GraphView;

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
        $this->tpl->addJs('spectrum.js');
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
        parent::createAction();
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
