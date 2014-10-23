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

namespace CentreonConfiguration\Controllers;

use \CentreonConfiguration\Models\Timeperiod;
use \CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded;
use \CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded;

class TimeperiodController extends \CentreonConfiguration\Controllers\BasicController
{
    protected $objectDisplayName = 'Timeperiod';
    protected $objectName = 'timeperiod';
    protected $objectBaseUrl = '/configuration/timeperiod';
    protected $datatableObject = '\CentreonConfiguration\Internal\TimeperiodDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Timeperiod';
    protected $repository = '\CentreonConfiguration\Repository\TimePeriodRepository';    
    public static $relationMap = array(
        'tp_include' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded',
        'tp_exclude' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded'
    );

    /**
     * List timeperiods
     *
     * @method get
     * @route /configuration/timeperiod
     */
    public function listAction()
    {
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/timeperiod/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/timeperiod/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new timeperiod
     *
     * @method post
     * @route /configuration/timeperiod/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a timeperiod
     *
     *
     * @method post
     * @route /configuration/timeperiod/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a timeperiod
     *
     * @method get
     * @route /configuration/timeperiod/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/configuration/timeperiod/add');
        parent::addAction();
    }
    
    /**
     * Update a timeperiod
     *
     * @method get
     * @route /configuration/timeperiod/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Duplicate a timeperiod
     *
     * @method post
     * @route /configuration/timeperiod/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/timeperiod/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/timeperiod/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/timeperiod/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Delete action for timeperiod
     *
     * @method post
     * @route /configuration/timeperiod/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/timeperiod/[i:id]/include
     */
    public function includedTimeperiodAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $includedTimeperiodList = Timeperiodincluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('timeperiod_include_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($includedTimeperiodList as $includedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $includedTimeperiod['tp_id'],
                "text" => $includedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
    
    /**
     * 
     * @method get
     * @route /configuration/timeperiod/[i:id]/exclude
     */
    public function excludedTimeperiodAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $excludedTimeperiodList = Timeperiodexcluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('timeperiod_exclude_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($excludedTimeperiodList as $excludedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $excludedTimeperiod['tp_id'],
                "text" => $excludedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
}
