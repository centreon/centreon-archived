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

use \Models\Configuration\Contact;

class UserController extends \Centreon\Core\Controller
{

    /**
     * List users
     *
     * @method get
     * @route /configuration/user
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
        $tpl->assign('objectName', 'User');
        $tpl->assign('objectAddUrl', '/configuration/user/add');
        $tpl->assign('objectListUrl', '/configuration/user/list');
        $tpl->display('configuration/list.tpl');
    }

    /**
     * 
     * @method get
     * @route /configuration/user/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'user',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * 
     * @method get
     * @route /configuration/user/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $contactObj = new Contact();
        $filters = array('contact_name' => $requestParams['q'].'%');
        $contactList = $contactObj->getList('contact_id, contact_name, contact_email', -1, 0, null, "ASC", $filters, "AND");
        
        $finalContactList = array();
        foreach($contactList as $contact) {
            $finalContactList[] = array(
                "id" => $contact['contact_id'],
                "text" => $contact['contact_name'],
                "theming" => \Centreon\Repository\UserRepository::getUserIcon($contact['contact_name'], $contact['contact_email'])
            );
        }
        
        $router->response()->json($finalContactList);
    }
    
    /**
     * Create a new user
     *
     * @method post
     * @route /configuration/user/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a user
     *
     *
     * @method put
     * @route /configuration/user/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a user
     *
     *
     * @method get
     * @route /configuration/user/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a user
     *
     *
     * @method get
     * @route /configuration/user/[i:id]
     */
    public function editAction()
    {
        
    }
}
