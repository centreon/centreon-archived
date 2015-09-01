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

namespace CentreonSecurity\Controllers;

use CentreonAdministration\Internal\User;
use Centreon\Internal\Form;
use Centreon\Internal\Di;
use CentreonSecurity\Internal\Sso;
use Centreon\Internal\Session;
use Centreon\Internal\Acl;
use Centreon\Internal\Controller;

/**
 * Login controller
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Controllers
 */
class LoginController extends Controller
{
    /**
     * Action for login page
     *
     * @method GET
     * @route /login
     */
    public function loginAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $redirectUrl = $router->request()->param(
            'redirect',
            str_replace('/login', '/', $router->request()->uri())
        );
        $tmpl = $di->get('template');
        
        $tmpl->assign('redirect', $redirectUrl);
        $tmpl->assign('base_url', $di->get('config')->get('global', 'base_url'));
        $tmpl->display('file:[CentreonSecurityModule]login.tpl');
    }

    /**
     * Action ajax for login
     *
     * @method POST
     * @route /login
     */
    public function loginPostAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $username = $router->request()->param('login');
        $password = $router->request()->param('passwd');
        try {
            $auth = new Sso($username, $password, 0);
            $user = new User($auth->userInfos['user_id']);
            $_SESSION['user'] = $user;
            Session::init($user->getId());
            $_SESSION['acl'] = new Acl($user);
            $backUrl = $user->getHomePage();
            $router->response()->json(
                array(
                    'status' => true,
                    'redirectRoute' => $backUrl
                )
            );
            
        } catch (\Exception $e) {
            $router->response()->json(
                array(
                    'status' => false,
                    'error' => $e->getMessage()
                )
            );
        }
    }

    /**
     * Logout the user
     *
     * @method GET
     * @route /logout
     */
    public function logoutAction()
    {
        session_regenerate_id(true);
        session_destroy();
        $this->router->response()->json(array('status' => true, 'success' => true));
    }
}
