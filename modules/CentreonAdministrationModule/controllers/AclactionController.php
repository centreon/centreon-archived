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

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator;
use CentreonAdministration\Repository\AclactionRepository;
use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

class AclactionController extends FormController
{
    protected $objectDisplayName = 'AclAction';
    public static $objectName = 'aclaction';
    protected $objectBaseUrl = '/centreon-administration/aclaction';
    protected $objectClass = '\CentreonAdministration\Models\Aclaction';
    public static $relationMap = array(
        'aclaction_aclgroups' => '\CentreonAdministration\Models\Relation\Aclgroup\Aclaction'
    );
    protected $datatableObject = '\CentreonAdministration\Internal\AclactionDatatable';
    public static $isDisableable = true;

    /**
     * List aclaction
     *
     * @method get
     * @route /aclaction
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /aclaction/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new acl action
     *
     * @method post
     * @route /aclaction/create
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update an acl action
     *
     * @method post
     * @route /aclaction/update
     */
    public function updateAction()
    {
        $params = $this->getParams('post');
        AclactionRepository::updateRules($params['object_id'], $params);
        parent::updateAction();
    }
    
    /**
     * Add a aclaction
     *
     *
     * @method get
     * @route /aclaction/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-administration/aclaction/add');
        parent::addAction();
    }
    
    /**
     * Update a aclaction
     *
     * @method get
     * @route /aclaction/[i:id]
     */
    public function editAction()
    {
        $tpl = Di::getDefault()->get('template');

        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';

        $myForm = new Generator($objectFormUpdateUrl, array('id' => $requestParam['id']));
        $myForm->addHiddenComponent('object_id', $requestParam['id']);
        $myForm->addHiddenComponent('object', $this->objectName);

        $myForm->setDefaultValues($this->objectClass, $requestParam['id']);

        /* action rules */
        $rules = AclactionRepository::getRulesFromActionId($requestParam['id']);
        $myForm->setDefaultValues($rules);

        $formModeUrl = Di::getDefault()
                        ->get('router')
                        ->getPathFor(
                            $this->objectBaseUrl.'/[i:id]',
                            array(
                                'id' => $requestParam['id']
                            )
                        );

        $tpl->assign('pageTitle', $this->objectDisplayName);
        $tpl->assign('form', $myForm->generate());
        $tpl->assign('advanced', $requestParam['advanced']);
        $tpl->assign('formModeUrl', $formModeUrl);
        $tpl->assign('formName', $myForm->getName());
        $tpl->assign('validateUrl', $objectFormUpdateUrl);
        $tpl->display('file:[CentreonConfigurationModule]edit.tpl');
    }

    /**
     * Retrieve list of acl action for a form
     *
     * @method get
     * @route /aclaction/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * Get default list of Acl groups
     *
     * @method get
     * @route /aclaction/[i:id]/aclgroup
     */
    public function aclgroupAction()
    {
        parent::getRelations($this->relationMap['aclaction_aclgroups']);
    }
}
