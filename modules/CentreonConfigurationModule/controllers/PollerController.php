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

class PollerController extends FormController
{
    protected $objectDisplayName = 'Poller';
    public static $objectName = 'poller';
    protected $objectBaseUrl = '/centreon-configuration/poller';
    protected $datatableObject = '\CentreonConfiguration\Internal\PollerDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Poller';
    protected $repository = '\CentreonConfiguration\Repository\PollerRepository';
    public static $relationMap = array();

    /**
     * List users
     *
     * @method get
     * @route /poller
     */
    public function listAction()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addJs('poller-generate.js', 'bottom', 'centreon-configuration');
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
        $params = $this->getParams('post');
        $router = Di::getDefault()->get('router');

        /* Save information */
        try {
            PollerRepository::update($params);
        } catch (Exception $e) {
            return $router->response()->json(array('success' => false, 'error' => $e->getMessage()));
        }

        return $router->response()->json(array('success' => true));
    }
    
    /**
     * Add a poller
     *
     * @method get
     * @route /poller/add
     */
    public function addAction()
    {
        /* Prepare form for wizard */
        $form = $this->getForm('add_poller');
        $this->tpl->assign('form', $form->toSmarty());
        $this->tpl->display('addPoller.tpl');
    }

    /**
     * Create a new poller
     *
     * @method post
     * @route /poller/add
     */
    public function createAction()
    {
        $params = $this->getParams('post');
        $router = Di::getDefault()->get('router');

        /* Check security */
        /*try {
            Form::validateSecurity($params['token']);
        } catch (Exception $e) {
            return $router->response()->json(array('success' => false, 'error' => $e->getMessage()));
        }*/
        try {
            PollerRepository::create($params);
        } catch (Exception $e) {
            return $router->response()->json(array('success' => false, 'error' => $e->getMessage()));
        }

        return $router->response()->json(array('success' => true));
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
        /* Prepare form for edition */
        $form = $this->getForm('edit_poller', $params['id']);
        $form->setDefaults(array(
            'poller_name' => $poller['name'],
            'ip_address' => $node['ip_address']
        ));
        
        $this->tpl->assign('brokerFormUrl', $this->router->getPathFor('/centreon-broker/[i:id]', array('id' => $params['id'])));
        $this->tpl->assign('engineFormUrl', $this->router->getPathFor('/centreon-engine/[i:id]', array('id' => $params['id'])));
        $form->addHidden('poller_id', $params['id']);
        $this->tpl->assign('object_id', $params['id']);
        $this->tpl->assign('form', $form->toSmarty());
        $this->tpl->assign('hookParams', array('pollerId' => $params['id']));
        $this->tpl->display('editPoller.tpl');
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
     * @route /poller/templates/form
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
        if (isset($params['poller'])) {
            $pollerId = $params['poller'];
        }
        
        $router->response()->json($myTemplate->genForm($pollerId));
    }

    /**
     * Return the form for add or edit a poller
     *
     * @param string $formName The form ID
     * @param int $pollerId The poller id
     * @return \Centreon\Internal\Form
     */
    private function getForm($formName, $pollerId = 0)
    {
        $form = new Form($formName);
        $form->add(array(
            'type' => 'text',
            'label' => 'Poller name',
            'name' => 'poller_name',
            'mandatory' => true
        ));
        $form->add(array(
            'type' => 'text',
            'label' => 'IP Address',
            'name' => 'ip_address',
            'mandatory' => true
        ));
        $selectParams = array(
            'object_type' => 'object',
            'defaultValuesRoute' => '/centreon-configuration/poller/templates',
            'listValuesRoute' => '/centreon-configuration/poller/[i:id]/template',
            'multiple' => false,
            'initCallback' => 'loadTemplateSteps'
        );
        $form->add(array(
            'type' => 'templatepoller',
            'label' => 'Poller Template',
            'name' => 'poller_tmpl',
            'mandatory' => true,
            'attributes' => json_encode($selectParams)
        ), array(
            'id' => $pollerId
        ));
        return $form;
    }
}
