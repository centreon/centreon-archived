<?php
/*
 * Copyright 2005-2014 CENTREON
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

use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\Controller;
use CentreonAdministration\Internal\TagDatatable;

/**
 * Controller for tag action
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonAdministration
 */
class TagController extends Controller
{
    protected static $datatableObject = '\CentreonAdministration\Internal\TagDatatable';
    
    protected $objectClass = '\CentreonAdministration\Models\Tag';
    
    
    /**
     *
     * @var type 
     */
    protected $objectBaseUrl = '/tag'; 
    
    /**
     * Add a tag
     *
     * @method post
     * @route /tag/add
     */
    public function addAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();

        if (!is_array($post['resourceId'])) {
            $listResources = array($post['resourceId']);
        } else {
            $listResources = $post['resourceId'];
        }
        
     
        foreach ($listResources as $resourceId) {
            $tagId = TagsRepository::add(
                $post['tagName'],
                $post['resourceName'],
                $resourceId
            );
        }
        return $router->response()->json(array('success' => true, 'tagId' => $tagId));
    }

    /**
     * Delete a tag
     *
     * @method post
     * @route /tag/delete
     */
    public function deleteAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $post = $router->request()->paramsPost();
        
        TagsRepository::delete(
            $post['tagId'],
            $post['resourceName'],
            $post['resourceId']
        );
        return $router->response()->json(array('success' => true));
    }
    
    /**
     * get list tag
     * 
     * @method get
     * @route /tag/[i:id]/[a:objectName]/formlist
     */
    public function listAction()
    {
        $data = '';
        $get = $this->getParams('named');
        
        if (isset($get['objectName']) && isset($get['id'])) {
            $data = TagsRepository::getList($get['objectName'], $get['id'], 1);
        }
        $this->router->response()->json($data);
    }
    
    /**
     * get all tag
     * 
     * @method get
     * @route /tag/[a:objectName]/all
     */
    public function allAction()
    {
        $data = '';
        $get = $this->getParams('named');
        
        if (isset($get['objectName'])) {
            $data = TagsRepository::getList($get['objectName'], 0, 1);
        }
        $this->router->response()->json($data);
    }
        
    /**
     * 
     * @method get
     * @route /tag
     */
    public function datatableAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new TagDatatable($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
          
        $router->response()->json($myDataForDatatable);
    }
}
