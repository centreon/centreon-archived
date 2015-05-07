<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
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
use CentreonAdministration\Repository\TagsRepository;

class BusinessActivityController extends FormController
{
    protected $objectDisplayName = 'BusinessActivity';
    public static $objectName = 'businessactivity';
    public static $enableDisableFieldName = 'activate';
    protected $objectClass = '\CentreonBam\Models\BusinessActivity';
    protected $datatableObject = '\CentreonBam\Internal\BusinessActivityDatatable';
    protected $repository = '\CentreonBam\Repository\BusinessActivityRepository'; 
    public static $relationMap = array(
        'kpi' => '\CentreonBam\Models\Relation\BusinessActivity\Indicator'
    );
   
    public static $isDisableable = true;

    /**
    * Create a new business activity
    *
    * @method post
    * @route /businessactivity/add
    */
    public function createAction()
    {
        $aTagList = array();
        $aTags = array();
        
        $givenParameters = $this->getParams('post');

        $repository = $this->repository;
        try {
            $id = $repository::create($givenParameters, 'wizard', $this->getUri());
            
            if (isset($givenParameters['ba_tags'])) {
                $aTagList = explode(",", $givenParameters['ba_tags']);
                foreach ($aTagList as $var) {
                    if (strlen($var) > 1) {
                        array_push($aTags, $var);
                    }
                }
                if (count($aTags) > 0) {
                    TagsRepository::saveTagsForResource('ba', $id, $aTags, '', false, 1);
                }
            }

            BusinessActivityRepository::createVirtualService($id);
            $aData = array('success' => true);
        } catch (\Exception $e) {
            $aData = array('success' => false, 'error' => $e->getMessage());
        }

        $this->router->response()->json($aData);
    }
 
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
                'del' => $router->getPathFor('/centreon-administration/tag/delete'),
                'getallGlobal' => $router->getPathFor('/centreon-administration/tag/all'),
                'getallPerso' => $router->getPathFor('/centreon-administration/tag/allPerso'),
                'addMassive' => $router->getPathFor('/centreon-administration/tag/addMassive')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }

    /**
     * Business activity tooltip
     *
     * @method get
     * @route /businessactivity/[i:id]/tooltip
     */
    public function displayTooltipAction()
    {
        $this->tpl->display('file:[CentreonBamModule]ba_tooltip.tpl');
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
     * Get list of Indicators for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/indicator
     */
    public function indicatorForBaAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');

        $indicatorList = BusinessActivityRepository::getIndicatorsForBa($requestParam['id']);
        $finalList = array();
        foreach ($indicatorList as $indicator) {
            $finalList[] = IndicatorRepository::getIndicatorName($indicator['kpi_id']);
        }

        $router->response()->json($finalList);
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
     * Update a business activity
     *
     *
     * @method post
     * @route /businessactivity/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        $aTagList = array();
        $aTags = array();
        
        parent::updateAction();
        
        if (isset($givenParameters['ba_tags'])) {
            $aTagList = explode(",", $givenParameters['ba_tags']);
            foreach ($aTagList as $var) {
                if (strlen($var) > 1) {
                    array_push($aTags, $var);
                }
            }
            if (count($aTags) > 0) {
                TagsRepository::saveTagsForResource('ba', $givenParameters['object_id'], $aTags, '', false, 1);
            }
        }        
    }

    /**
     * Delete a business activity
     *
     *
     * @method post
     * @route /businessactivity/delete
     */
    public function deleteAction()
    {
        $givenParameters = $this->getParams('post');

        BusinessActivityRepository::deleteVirtualService($givenParameters['ids']);
        parent::deleteAction();
    }
    
    /**
     * Get list of icons for a specific business activity
     *
     *
     * @method get
     * @route /businessactivity/[i:id]/icon
     */
    public function iconForBaAction()
    {
        parent::getSimpleRelation('icon_id', '\CentreonBam\Models\Icon');
    }
}
