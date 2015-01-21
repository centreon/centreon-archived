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

class BusinessActivityController extends FormController
{
    protected $objectDisplayName = 'BusinessActivity';
    protected $objectName = 'BusinessActivity';
    protected $objectBaseUrl = '/centreon-bam/business-activity';
    protected $objectClass = '\CentreonBam\Models\BusinessActivity';
    protected $datatableObject = '\CentreonBam\Internal\BusinessActivityDatatable';
    protected $repository = '\CentreonBam\Repository\BusinessActivityRepository'; 
    public static $relationMap = array();
    
    /**
     * 
     * @method get
     * @route /business-activity
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
     * @route /business-activity/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * 
     * @method get
     * @route /business-activity/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Update a business activity
     *
     *
     * @method post
     * @route /business-activity/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Create a new business activity
     *
     * @method post
     * @route /business-activity/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Add a business activity
     *
     * @method get
     * @route /business-activity/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-bam/business-activity/add');
        parent::addAction();
    }
    
    /**
     * Update a business activity
     *
     * @method get
     * @route /business-activity/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Duplicate a business activity
     *
     * @method post
     * @route /business-activity/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /business-activity/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /business-activity/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /business-activity/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Delete action for business activity
     *
     * @method post
     * @route /business-activity/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}
