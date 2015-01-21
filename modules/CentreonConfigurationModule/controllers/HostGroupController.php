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

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

class HostGroupController extends FormController
{
    protected $objectDisplayName = 'Hostgroup';
    protected $objectName = 'hostgroup';
    protected $objectBaseUrl = '/centreon-configuration/hostgroup';
    protected $objectClass = '\CentreonConfiguration\Models\Hostgroup';
    protected $repository = '\CentreonConfiguration\Repository\HostgroupRepository';

    /**
     *
     * @var type 
     */
    protected $datatableObject = '\CentreonConfiguration\Internal\HostGroupDatatable';
    
    public static $relationMap = array(
        'hg_hosts' => '\CentreonConfiguration\Models\Relation\Host\Hostgroup',
    );
    
    public static $isDisableable = true;

    /**
     * List hostgroups
     *
     * @method get
     * @route /hostgroup
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
     * @route /hostgroup/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /hostgroup/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Update a hostgroup
     *
     *
     * @method post
     * @route /hostgroup/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a hostgroup
     *
     * @method get
     * @route /hostgroup/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-configuration/hostgroup/add');
        parent::addAction();
    }
    
    /**
     * Add a hostgroup
     *
     *
     * @method post
     * @route /hostgroup/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a hostgroup
     *
     *
     * @method get
     * @route /hostgroup/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /hostgroup/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /hostgroup/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hostgroup
     *
     * @method POST
     * @route /hostgroup/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /hostgroup/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for hostgroup
     *
     * @method post
     * @route /hostgroup/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for hostcategory
     * 
     * @method post
     * @route /hostgroup/enable
     */
    public function enableAction()
    {
        parent::enableAction('hg_activate');
    }
    
    /**
     * Disable action for hostgroup
     * 
     * @method post
     * @route /hostgroup/disable
     */
    public function disableAction()
    {
        parent::disableAction('hg_activate');
    }
    
    /**
     * Get list of hostgroups for a specific host
     *
     *
     * @method get
     * @route /hostgroup/[i:id]/host
     */
    public function hostForHostGroupAction()
    {
        parent::getRelations(static::$relationMap['hg_hosts']);
    }
}
