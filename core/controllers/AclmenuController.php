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

namespace Centreon\Controllers;

use \Centreon\Internal\Form;

class AclmenuController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'AclMenu';
    protected $objectName = 'aclmenu';
    protected $objectBaseUrl = '/administration/aclmenu';
    protected $objectClass = '\CentreonConfiguration\Models\Acl\Menu';
    public static $relationMap = array(
        'aclmenu_aclgroups' => '\CentreonConfiguration\Models\Relation\Aclgroup\Aclmenu'
    );

    /**
     * List aclmenu
     *
     * @method get
     * @route /administration/aclmenu
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /administration/aclmenu/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new ACL menu
     *
     * @method post
     * @route /administration/aclmenu/create
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Set Acl data
     * 
     * @param array $aclData
     * @param array $params
     */
    private function setAclMenuData(&$aclData, $params)
    {
        $aclTypes = array('acl_create', 'acl_update', 'acl_delete', 'acl_view', 'acl_advanced');
        foreach ($aclTypes as $aclType) {
            if (isset($params[$aclType])) {
                foreach ($params[$aclType] as $menuId => $on) {
                    if (!isset($aclData[$menuId])) {
                        $aclData[$menuId] = 0;
                    }
                    switch ($aclType) {
                        case 'acl_create':
                            $flag = \Centreon\Internal\Acl::ADD;
                            break;
                        case 'acl_update':
                            $flag = \Centreon\Internal\Acl::UPDATE;
                            break;
                        case 'acl_delete':
                            $flag = \Centreon\Internal\Acl::DELETE;
                            break;
                        case 'acl_view':
                            $flag = \Centreon\Internal\Acl::VIEW;
                            break;
                        case 'acl_advanced':
                            $flag = \Centreon\Internal\Acl::ADVANCED;
                            break;
                        default:
                            throw new \Centreon\Internal\Exception(
                                sprintf('Unknown acl type %s', $aclType)
                            );
                            break;
                    }
                    $aclData[$menuId] = $aclData[$menuId] | $flag;
                }
            }
        }
    }

    /**
     * Update an ACL menu
     *
     *
     * @method post
     * @route /administration/aclmenu/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        if (Form::validateSecurity($givenParameters['token'])) {
            $aclmenu = array(
                'name' => $givenParameters['name'],
                'description' => $givenParameters['description'],
                'enabled' => $givenParameters['enabled'],
            );
            
            $aclmenuObj = new \CentreonConfiguration\Models\Acl\Menu();
            $aclMenuGroupRelation = new \CentreonConfiguration\Models\Relation\Aclgroup\Aclmenu();
            try {
                $aclmenuObj->update($givenParameters['acl_menu_id'], $aclmenu);
                $aclData = array();
                $this->setAclMenuData($aclData, $givenParameters);
                \Centreon\Repository\AclmenuRepository::updateAclLevel(
                    $givenParameters['acl_menu_id'],
                    $aclData
                );
                $aclMenuGroupRelation->delete(null, $givenParameters['acl_menu_id']);
                $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
                $db->beginTransaction();
                $aclgroups = explode(",", $givenParameters['acl_groups']);
                foreach ($aclgroups as $aclgroupId) {
                    if (is_numeric($aclgroupId)) {
                        $aclMenuGroupRelation->insert($aclgroupId, $givenParameters['acl_menu_id']);
                    }
                }
                $db->commit();
            } catch (Exception $e) {
                echo "fail";
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }
    
    /**
     * Add a aclmenu
     *
     *
     * @method get
     * @route /administration/aclmenu/add
     */
    public function addAction()
    {
        // Init template
        $di = \Centreon\Internal\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('aclmenuForm');
        $form->addText('name', _('Name'));
        $form->addText('description', _('Description'));
        
        $radios['list'] = array(
          array(
              'name' => 'Enabled',
              'label' => 'Enabled',
              'value' => '1'
          ),
          array(
              'name' => 'Disabled',
              'label' => 'Disabled',
              'value' => '0'
          )
        );
        $form->addRadio('enabled', _("Status"), 'status', '&nbsp;', $radios);
        
        $form->add('save_form', 'submit', _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        
        // Display page
        $tpl->display('administration/aclmenu/edit.tpl');
    }
    
    /**
     * Update a aclmenu
     *
     *
     * @method get
     * @route /administration/aclmenu/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Retrieve list of acl menu for a form
     *
     * @method get
     * @route /administration/aclmenu/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Retrieve list of acl menu for a form
     *
     * @method get
     * @route /administration/aclmenu/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Duplicate acl menu
     *
     * @method get
     * @route /administration/aclmenu/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }
    
    /**
     * Duplicate acl menu
     *
     * @method get
     * @route /administration/aclmenu/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * MC Field action for aclmenu
     *
     * @method post
     * @route /administration/aclmenu/mc_fields
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Get default list of Acl groups
     *
     * @method get
     * @route /administration/aclmenu/[i:id]/aclgroup
     */
    public function aclgroupAction()
    {
        parent::getRelations($this->relationMAp['aclmenu_aclgroups']);
    }
}
