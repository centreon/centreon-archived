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

namespace CentreonSecurity\Api\rest;

use Centreon\Internal\Api;
use CentreonAdministration\Repository\UserRepository;
use Centreon\Internal\Exception\Authentication\BadCredentialException;
use Centreon\Internal\Exception\Http\UnauthorizedException;

/**
 * Description of AuthenticationApi
 *
 * @author lionel
 */
class AuthenticationApi extends Api
{
    /**
     * 
     * @param type $request
     */
    public function __construct($request)
    {
        parent::__construct($request);
    }
    
    /**
     * @route /authenticate
     * @method POST
     */
    public function authenticateAction()
    {
        try {
            $params = $this->getParams();
            $login = $params['login'];
            $password = $params['password'];
            $token = UserRepository::getTokenForApi($login, $password);
            $this->router->response()->json(array('token' => $token));
        } catch(BadCredentialException $ex) {
            //throw new UnauthorizedException('Api Authentication failed', $ex->getMessage(), $ex->getCode(), $ex);
            $errorObject = array(
                'id' => '',
                'href' => '',
                'status' => '401',
                'code' => (string)$ex->getCode(),
                'title' => 'Api Authentication failed',
                'detail' => $ex->getMessage(),
                'links' => '',
                'path' => ''
            );
            $this->router->response()->code(401)->json($errorObject);
        }
    }
}
