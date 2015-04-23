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
use Centreon\Internal\Di;
use CentreonAdministration\Events\ContactinfoListKey;
use Centreon\Controllers\FormController;

class ContactController extends FormController
{
    protected $objectDisplayName = 'Contact';
    public static $objectName = 'contact';
    protected $objectBaseUrl = '/centreon-administration/contact';
    protected $objectClass = '\CentreonAdministration\Models\Contact';
    protected $repository = '\CentreonAdministration\Repository\ContactRepository';
    
    public static $relationMap = array();
    
    protected $datatableObject = '\CentreonAdministration\Internal\ContactDatatable';
    public static $isDisableable = true;
    
     /**
     * List contact
     *
     * @method get
     * @route /contact
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
                ->addJs('hogan-3.0.0.min.js')
            ->addCss('centreon.tag.css', 'centreon-administration');
        
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete'),
                'getallGlobal' => $router->getPathFor('/centreon-administration/tag/all'),
                'getallPerso' => $router->getPathFor('/centreon-administration/tag/allPerso'),
                'addMassive' => $router->getPathFor('/centreon-administration/tag/addMassive')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /contact/contact-info/formlist
     * 
     * @todo remove cutom data and emit event to retrieve the list
     */
    public function notificationWayFormListAction()
    {
        /*$di = Di::getDefault();
        $event = $di->get('events');*/
        $InfoKeys = new ContactinfoListKey();
        //$event->emit('centreon-administration.contactinfo.key.list', array($InfoKeys));
        $InfoKeys->addKey('sms')->addKey('email')->addKey('twitter')->addKey('whatsapp')->addKey('feelslikevegasdontit');
        $this->router->response()->json($InfoKeys->getKeyList());
    }
    
    /**
     * 
     * @method get
     * @route /contact/contact-info/default
     * 
     * @return array
     */
    public function defaultNotificationList()
    {
         $this->router->response()->json(array());
    }

    /**
     * Update a contact info
     *
     * @method post
     * @route /contact/add/info
     */
    public function addContactInfoAction()
    {
        $givenParameters = $this->getParams();
        unset($givenParameters['token']);
        $repository = $this->repository;
        $contactId = $repository::updateContact($givenParameters);
        $removeUrl = $this->router->getPathFor('/centreon-administration/contact/info/remove/[i:id]', array('id' => $contactId));
        $this->router->response()->json(array(
            'success' => true,
            'value' => $givenParameters['contact_info_value'],
            'origin' => $givenParameters['contact_info_key'],
            'removeurl' => $removeUrl
        ));
    }
    
    
     /**
     * Update a contact
     *
     * @method post
     * @route /contact/update
     */
    public function updateContactAction()
    {
        $givenParameters = $this->getParams();

        unset($givenParameters['token']);
        $repository = $this->repository;
        $contactId = $repository::updateContact($givenParameters);
        
        $this->router->service()->back();

    }
    
    /**
     * Edit a contact
     *
     *
     * @method get
     * @route /contact/[i:id]
     */
    public function editAction()
    {
        $this->tpl->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.tag.css', 'centreon-administration');
        
        $requestParam = $this->getParams('named');
        $customForm = new Form('ContactInfoForm');
        
        // Add selector
        $selectAttributes = json_encode(array(
            'defaultValuesRoute' =>  '/centreon-administration/contact/contact-info/formlist',
            'listValuesRoute' =>  ''
        ));
        
        // Add selector
        $aSelectTimezones = json_encode(array(
            'defaultValuesRoute' =>  '/centreon-administration/timezone/formlist',
            'listValuesRoute' =>  $this->router->getPathFor(
                    '/centreon-administration/contact/[i:id]/timezone', 
                    array('id' => $requestParam['id'])
            )
        ));
        
        // Add selector
        $aSelectTags = json_encode(array(
            'defaultValuesRoute' =>  '/centreon-administration/tag/all',
            'listValuesRoute' =>  $this->router->getPathFor(
                    '/centreon-administration/tag/[i:id]/contact/formlist', 
                    array('id' => $requestParam['id'])
            )
        ));
        
        $customForm->addStatic(array(
            'name' => 'contact_info_key',
            'label' => _('Notification way'),
            'type' => 'select',
            'mandatory' => false,
            'advanced' => false,
            'attributes' => $selectAttributes
        ));
        
        $customForm->addStatic(array(
            'name' => 'contact_info_value',
            'label' => _('Value'),
            'type' => 'text',
            'mandatory' => false,
            'advanced' => false,
        ));
        
        $customForm->addStatic(array(
            'name' => 'timezone_id',
            'label' => _('Timezone'),
            'type' => 'select',
            'mandatory' => false,
            'advanced' => false,
            'attributes' => $aSelectTimezones,
        ));
        $customForm->addStatic(array(
            'name' => 'contact_tags',
            'label' => _('Tags'),
            'type' => 'tag',
            'mandatory' => false,
            'advanced' => false,
            'attributes' => $aSelectTags,
        ));
        
        $customForm->addSubmit('add_button', 'Add');
        $customForm->addHidden('object_id', $requestParam['id']);
        $customForm->addHidden('object', static::$objectName);
        
        // Get Already loaded
        $repository = $this->repository;
        $contactInfos =  $repository::getContactInfo($requestParam['id'], true);
        
        
        $objectFormUpdateUrl = $this->objectBaseUrl.'/add/info';
        $objectFormupdateContact = $this->objectBaseUrl.'/update';
        $deleteUrl = $this->router->getPathFor('/centreon-administration/contact/info/remove/[i:id]', array('id' => $requestParam['id']));
        $this->tpl->assign('deleteUrl', $deleteUrl);
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        $this->tpl->assign('updateContact', $objectFormupdateContact);
        $this->tpl->assign('formName', 'ContactInfoForm');
        $this->tpl->assign('contactInfos', $contactInfos);
        $this->tpl->assign('form', $customForm->toSmarty());
        $this->tpl->display('file:contactform.tpl');
    }
    
    /**
     * Remove a contact info
     *
     * @method get
     * @route /contact/info/remove/[i:id]
     */
    public function removeContactInfoAction()
    {
        $requestParam = $this->getParams('named');
        $repository = $this->repository;
        $repository::removeContactInfo($requestParam['id']);
        $this->router->service()->back();
    }
    
    /**
     * Get list of timezone for a specific contact
     *
     *
     * @method get
     * @route /contact/[i:id]/timezone
     */
    public function timezoneForContactAction()
    {
        parent::getSimpleRelation('timezone_id', '\CentreonAdministration\Models\Timezone');
    }
}
