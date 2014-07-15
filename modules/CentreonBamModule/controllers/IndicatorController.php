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

class IndicatorController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Indicator';
    protected $objectName = 'indicator';
    protected $objectBaseUrl = '/bam/indicator';
    protected $objectClass = '\CentreonBam\Models\Indicator';
    
    public static $moduleName = 'CentreonBam';
    
    /**
     * 
     * @method get
     * @route /bam/indicator
     */
    public function listAction()
    {
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /bam/indicator/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * 
     * @method get
     * @route /bam/indicator/formlist
     */
    /*public function formListAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $pollerObj = new Poller();
        $filters = array('name' => $requestParams['q'].'%');
        $pollerList = $pollerObj->getList('id, name', -1, 0, null, "ASC", $filters, "AND");
        
        $finalPollerList = array();
        foreach ($pollerList as $poller) {
            $finalPollerList[] = array(
                "id" => $poller['id'],
                "text" => $poller['name']
            );
        }
        
        $router->response()->json($finalPollerList);
    }*/
    
    /**
     * Create a new poller
     *
     * @method post
     * @route /bam/indicator/add
     */
    public function createAction()
    {
        
    }
    
    /**
     * Update a poller
     *
     *
     * @method post
     * @route /bam/indicator/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a poller
     *
     * @method get
     * @route /bam/indicator/add
     */
    public function addAction()
    {
        $tpl = \Centreon\Internal\Di::getDefault()->get('template');
        $tpl->assign('validateUrl', '/bam/indicator/add');
        parent::addAction();
    }
    
    /**
     * Update a poller
     *
     * @method get
     * @route /bam/indicator/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Duplicate a poller
     *
     * @method post
     * @route /bam/indicator/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /bam/indicator/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /bam/indicator/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /bam/indicator/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Delete action for poller
     *
     * @method post
     * @route /bam/indicator/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}
