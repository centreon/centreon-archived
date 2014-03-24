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

namespace Controllers\Configuration;

class UsergroupController extends \Centreon\Core\Controller
{
    protected $objectDisplayName = 'User group';
    protected $objectName = 'usergroup';
    protected $objectBaseUrl = '/configuration/usergroup';
    protected $objectClass = '\Models\Configuration\Contactgroup';
    public static $relationMap = array(
        'cg_contacts' => '\Models\Configuration\Relation\Contact\Contactgroup'
    );

    /**
     * List usergroups
     *
     * @method get
     * @route /configuration/usergroup
     */
    public function listAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');

        // Load CssFile
        $tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css');

        // Load JsFile
        $tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js');
        
        // Display page
        $tpl->assign('objectName', 'Usergroup');
        $tpl->assign('objectAddUrl', '/configuration/usergroup/add');
        $tpl->assign('objectListUrl', '/configuration/usergroup/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/usergroup/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(
            \Centreon\Core\Datatable::getDatas(
                'usergroup',
                $this->getParams('get')
            )
        );
    }
    
    /**
     * 
     * @method get
     * @route /configuration/usergroup/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Create a new usergroup
     *
     * @method post
     * @route /configuration/usergroup/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a usergroup
     *
     *
     * @method put
     * @route /configuration/usergroup/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a usergroup
     *
     *
     * @method get
     * @route /configuration/usergroup/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a usergroup
     *
     *
     * @method get
     * @route /configuration/usergroup/[i:id]
     */
    public function editAction()
    {
        
    }

    /**
     * Get list of contacts for a specific contact group
     *
     * @method get
     * @route /configuration/usergroup/[i:id]/contact
     */
    public function contactForContactgroupAction()
    {
        parent::getRelations(static::$relationMap['cg_contacts']);
    }
}
