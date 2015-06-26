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
 * For more information : poller@centreon.com
 *
 */

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;

use CentreonConfiguration\Models\Poller as PollerModel;
use CentreonConfiguration\Models\Node as NodeModel;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Internal\PollerTemplateManager;
use Centreon\Internal\Form;
use Centreon\Internal\Exception;
use Centreon\Controllers\FormController;
use Centreon\Internal\Module\Informations;

class PollerController extends FormController
{
    protected $objectDisplayName = 'Poller';
    public static $objectName = 'poller';
    protected $objectBaseUrl = '/centreon-configuration/poller';
    protected $datatableObject = '\CentreonConfiguration\Internal\PollerDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Poller';
    protected $repository = '\CentreonConfiguration\Repository\PollerRepository';
    public static $relationMap = array();

    public static $isDisableable = true;
    public static $enableDisableFieldName = 'enable';

    /**
     * List pollerss
     *
     * @method get
     * @route /poller
     */
    public function listAction()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addJs('poller-generate.js', 'bottom', 'centreon-configuration');
        $tpl->addJs('poller-template.js', 'bottom', 'centreon-configuration');
        parent::listAction();
    }
    
    /**
     * Update a poller
     *
     * @method post
     * @route /poller/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a poller
     *
     * @method get
     * @route /poller/add
     */
    public function addAction()
    {
        parent::addAction();
    }

    /**
     * Create a new poller
     *
     * @method post
     * @route /poller/add
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update a poller
     *
     * @method get
     * @route /poller/[i:id]
     */
    public function editAction()
    {
        $params = $this->getParams();
        $poller = PollerModel::get($params['id']);
        $node = NodeModel::get($poller['node_id']);

        $this->tpl->addJs('poller-template.js', 'bottom', 'centreon-configuration');

        parent::editAction(array(), array('ip_address' => $node['ip_address']));
    }

    /**
     * Display wizard for applying configuration
     *
     * @method get
     * @route /poller/applycfg
     */
    public function applyConfAction()
    {
        $tpl = Di::getDefault()->get('template');
        $params = $this->getParams();
        $tpl->display('file:[CentreonConfigurationModule]applycfg.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /poller/templates
     */
    public function getPollerTemplatesAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        PollerRepository::getPollerTemplates();
        $data = $di->get('pollerTemplate');
        $returnData = array();
        foreach ($data as $id => $file) {
            $returnData[] = array(
                'id' => $id,
                'text' => ucfirst($id)
            );
        }
        $router->response()->json($returnData);
    }

    /**
     * Get default template for a poller
     *
     * @method get
     * @route /poller/[i:id]/template
     */
    public function getPollerDefaultTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $params = $this->getParams();
        $returnData = array();
        
        if ($params['id'] == 0) {
            PollerRepository::getPollerTemplates();
            $defaultPoller = array_slice($di->get('pollerTemplate'), 0, 1, true);
            $p = key($defaultPoller);
            
            $returnData['id'] = $p;
            $returnData['text'] = ucfirst($p);
        } else {
            $poller = PollerModel::get($params['id']);
            $returnData['id'] = $poller['tmpl_name'];
            $returnData['text'] = ucfirst($poller['tmpl_name']);
        }
        $router->response()->json($returnData);
    }
    
    /**
     * Get default template for a poller
     *
     * @method post
     * @route /poller/[i:id]/templates/form
     */
    public function getFormForTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $params = $this->getParams();
        
        PollerRepository::getPollerTemplates();
        $pollerTemplateList = $di->get('pollerTemplate');
        
        $myLiteTemplate = unserialize($pollerTemplateList[$params['name']]);
        $myTemplate = $myLiteTemplate->toFullTemplate();

        $pollerId = null;
        if (isset($params['id'])) {
            $pollerId = $params['id'];
        }
        
        $router->response()->json($myTemplate->genForm($pollerId));
    }
}
