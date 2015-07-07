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
namespace CentreonConfiguration\Commands;

use CentreonConfiguration\Api\Internal\BasicTagSupport;

/**
 * 
 */
class HostTemplateCommand extends BasicTagSupport
{
    /**
     *
     * @var type 
     */
    public $objectName = 'hosttemplate';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @cmdForm /centreon-configuration/hosttemplate/update required 
     * @cmdParam none host-custommacros optional
     * @cmdParam none host-tags optional
     * @cmdParam none enable optional 
     * @cmdParam boolean|false disable required disable the host 
     */
    public function createAction($params) {
        parent::createAction($params);
    }
    
    
    /**
     * 
     * @cmdForm /centreon-configuration/hosttemplate/update optional
     * @cmdObject string host-template the template
     * @cmdParam none host-custommacros optional 
     * @cmdParam none host-tags optional 
     * @cmdParam none enable optional 
     * @cmdParam boolean|false disable optional disable the host because it sucks
     * @cmdParam boolean|true enable optional enable the host
     */
    public function updateAction($object, $params) {
        parent::updateAction($object, $params);
    }
    
    /**
     * @cmdForm /centreon-configuration/hosttemplate/update map 
     * @cmdObject string host-template the host
     */
    public function showAction($object, $fields = null, $linkedObject = '') 
    {
        parent::showAction($object, $fields, $linkedObject);
    }
    
        /**
     * 
     * @cmdObject string host-template the host
     */
    public function deleteAction($object) 
    {
        parent::deleteAction($object);
    }
    
    /**
     * 
     * @cmdObject string host-template the host
     */
    public function listTagAction($object = null) 
    {
        parent::listTagAction($object);
    }
    
    /**
     * 
     * @cmdObject string host-template the host
     * @cmdParam string tag required the tag
     */
    public function addTagAction($object, $params) 
    {
        parent::addTagAction($object, $params['tag']);
    }
    
    /**
     * 
     * @cmdObject string host-template the host
     * @cmdParam string tag required the tag
     */
    public function removeTagAction($object, $params) 
    {
        parent::removeTagAction($object, $params['tag']);
    }
    
    /**
     * 
     * @cmdObject string host-template the host
     * @cmdParam string name required the macro name
     * @cmdParam string value required the macro value
     * @cmdParam boolean|true hidden required is the macro hidden ?
     */
    public function addMacroAction($object, $params) 
    {
        parent::addMacroAction($object, $params);
    }
    
    
    /**
     * 
     * @cmdObject string host-template the host
     * @cmdObject string macro the macro to update
     * @cmdParam string name optional the macro name
     * @cmdParam string value optional the macro value
     * @cmdParam boolean|true hidden optional is the macro hidden ?
     * @cmdParam boolean|false show optional is the macro showed ?
     */
    public function updateMacroAction($object, $params) {
        parent::updateMacroAction($object, $object['macro'], $params);
    }
    
    /**
     * 
     * @cmdObject string host-template the host
     */
    public function listMacroAction($object = null) {
        parent::listMacroAction($object);
    }
    
    
    /**
     * 
     * @cmdObject string host-template the host
     * @cmdObject string macro the macro to update
     */
    public function removeMacroAction($object, $params) {
        parent::removeMacroAction($object, $object['macro']);
    }
    
    
    
    
}
