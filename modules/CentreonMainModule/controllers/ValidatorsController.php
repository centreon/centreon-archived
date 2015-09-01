<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonMain\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;
//use Centreon\Internal\Form\Validators\Unique;
use Centreon\Internal\Form\Validators\ForbiddenChar;
//use Centreon\Internal\Form\Validators\CircularDependency;

use CentreonMain\Forms\Validators\Unique;
use CentreonMain\Forms\Validators\CircularDependency;

/**
 * Validators controller
 *
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Controllers
 */
class ValidatorsController extends Controller
{
    
    public static $sContext  = 'client';
    
    /**
     * 
     * @method post
     * @route /validator/email
     */
    public function emailAction()
    {
        $params = $this->getParams('post');
        $di = Di::getDefault();
        $router = $di->get('router');
        
        if (filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $jsonResponse = array('success' => true);
        } else {
            $jsonResponse = array('success' => false, 'error' => _("The email is not valid"));
        }
        
        $router->response()->code('200')->json($jsonResponse);
    }
    
    /**
     * 
     * @method post
     * @route /validator/resolvedns
     */
    public function resolveDnsAction()
    {
        $params = $this->getParams('post');
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $ipAddress = gethostbyname($params['dnsname']);
        
        if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $jsonResponse = array('success' => true, 'value' =>  $ipAddress);
        } else {
            $jsonResponse = array('success' => false, 'error' => _("Can't resolve the given dns name"));
        }
        
        return $jsonResponse['success'];
    }
    
    /**
     * 
     * @method post
     * @route /validator/ipaddress
     */
    public function ipAddressAction()
    {
        $params = $this->getParams('post');
        $jsonResponse = Ipaddress::validate($params['ipaddress']);

        return $jsonResponse['success'];
    }
        
    /**
     * 
     * @method post
     * @route /validator/forbiddenchar
     */
    public function forbiddenCharAction()
    {
        $params = $this->getParams('post');
        $jsonResponse = ForbiddenChar::validate($params['value']);

        return $jsonResponse['success'];
    }

    /**
     * Looks for circular definitions
     *
     * @method post
     * @route /validator/circular
     *
    public function circularDependencyAction()
    {
        $params = $this->getParams('post');

        $result = CircularDependency::validate(
            $params['value'],
            $params['module'],
            $params['object'],
            $params['id']
        );
        
        return $result;
    }
    */
    
     
    
    /**
     * 
     * @method post
     * @route /validator/unique
     */
    public function uniqueAction()
    {
       
        $params = $this->getParams('post')->all();
        
        $value = '';
        $aParams = array('object' => $params['object'], 'extraParams' => $params);
        
        $oValidator = new Unique();
        
        
        $this->router->response()->json($oValidator->validate($value, $aParams, static::$sContext));
        //die('test');
        //echo json_encode($oValidator->validate($value, $aParams, static::$sContext));
        
       
    }
    
    /**
     * 
     *
     * @method post
     * @route /validator/circular
     */
    public function circularAction()
    {
        $params = $this->getParams('post')->all();
        
        $value = '';
           
        $oValidator = new CircularDependency();
        echo json_encode($oValidator->validate($value, $params, static::$sContext));

    }
}
