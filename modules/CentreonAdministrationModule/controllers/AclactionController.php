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

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Form\Generator\Web\Full;
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
        $myForm->addHiddenComponent('object', static::$objectName);

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
