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
 */

namespace CentreonBam\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonBam\Repository\BusinessActivityRepository;
use CentreonBam\Repository\IndicatorRepository;
use CentreonBam\Models\Relation\BusinessActivity\BusinessActivitychildren;
use CentreonBam\Models\Relation\BusinessActivity\BusinessActivityparents;

class BusinessActivityController extends FormController
{
    protected $objectDisplayName = 'BusinessActivity';
    public static $objectName = 'businessactivity';
    protected $objectClass = '\CentreonBam\Models\BusinessActivity';
    protected $datatableObject = '\CentreonBam\Internal\BusinessActivityDatatable';
    protected $repository = '\CentreonBam\Repository\BusinessActivityRepository'; 
    public static $relationMap = array(
        'parent_business_activity' => '\CentreonBam\Models\Relation\BusinessActivity\BusinessActivitychildren',
        'child_business_activity' => '\CentreonBam\Models\Relation\BusinessActivity\BusinessActivityparents',
        'normal_kpi' => '\CentreonBam\Models\Relation\BusinessActivity\Indicator'
    );
    
    /**
     * 
     * @method get
     * @route /businessactivity
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.tag.css', 'centreon-administration');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }

    /**
     *
     * @method get
     * @route /businessactivity/realtime
     */
    public function displayAction()
    {
        $repository = $this->repository;
        $buList = $repository::getBuList();

        // Add css
        $this->tpl->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('gridstack.css','centreon-bam')
            ->addCss('bam.css','centreon-bam');

        // Add js
        $this->tpl->addJs('jquery.min.js')
            ->addJs('jquery-ui.min.js')
            ->addJs('d3.min.js')
            ->addJs('underscore-min.js','bottom','centreon-bam')
            ->addJs('jquery.easing.min.js','bottom','centreon-bam')
            ->addJs('gridstack.js','bottom','centreon-bam')
            ->addJs('bam.js','bottom','centreon-bam');

        // Send values to Smarty
        $this->tpl->assign('buList', $buList);

        // Display template
        $this->tpl->display("businessview.tpl");
    }
   
    /**
     * Get list of Types for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/type
     */
    public function typeForHostAction()
    {
        parent::getSimpleRelation('ba_type_id', '\CentreonBam\Models\BusinessActivityType');
    }

    /**
     * Get list of Types for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/normalindicator
     */
    public function indicatorForBaAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');

        $indicatorList = BusinessActivityRepository::getIndicatorsForBa($requestParam['id']);
        $finalList = array();
        foreach ($indicatorList as $indicator) {
            //var_dump($indicator);
            $finalList[] = IndicatorRepository::getNormalIndicatorName($indicator['kpi_id']);
        }

        $router->response()->json($finalList);
    }
 
    /**
     * Get list of Timeperiods for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/checkperiod
     */
    public function checkPeriodForHostAction()
    {
        parent::getSimpleRelation('id_check_period', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get list of Timeperiods for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/notificationperiod
     */
    public function notificationPeriodForHostAction()
    {
        parent::getSimpleRelation('id_notification_period', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get reporting period for a specific business activity
     *
     * @method get
     * @route /businessactivity/[i:id]/reportingperiod
     */
    public function reportingPeriodForHostAction()
    {
        parent::getSimpleRelation('id_reporting_period', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * 
     * @method get
     * @route /businessactivity/[i:id]/parent
     */
    public function parentForBusinessActivityAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $BusinessActivityparentsList = BusinessActivityparents::getMergedParameters(
            array('ba_id', 'name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_bam_dep_parents_relations.id_ba' => $requestParam['id']),
            "AND"
        );

        $finalBusinessActivityList = array();
        foreach ($BusinessActivityparentsList as $BusinessActivityparents) {
            $finalBusinessActivityList[] = array(
                "id" => $BusinessActivityparents['ba_id'],
                "text" => $BusinessActivityparents['name'],
                "theming" => BusinessActivityRepository::getIconImage(
                    $BusinessActivityparents['name']
                ).' '.$BusinessActivityparents['name']
            );
        }
        
        $router->response()->json($finalBusinessActivityList);
    }

    /**
     * 
     * @method get
     * @route /businessactivity/[i:id]/child
     */
    public function childForBusinessActivityAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $BusinessActivitychildrenList = BusinessActivitychildren::getMergedParameters(
            array('ba_id', 'name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_bam_dep_children_relations.id_dep' => $requestParam['id']),
            "AND"
        );

        $finalBusinessActivityList = array();
        foreach ($BusinessActivitychildrenList as $BusinessActivitychildren) {
            $finalBusinessActivityList[] = array(
                "id" => $BusinessActivitychildren['ba_id'],
                "text" => $BusinessActivitychildren['name'],
                "theming" => BusinessActivityRepository::getIconImage(
                    $BusinessActivitychildren['name']
                ).' '.$BusinessActivitychildren['name']
            );
        }
        
        $router->response()->json($finalBusinessActivityList);
    }
}
