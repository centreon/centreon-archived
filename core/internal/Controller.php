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
 *
 */


namespace Centreon\Internal;

abstract class Controller
{
    /**
     *
     * @var type 
     */
    protected $request;

    /**
     * 
     * @param type $request
     */
    public function __construct($request)
    {
        $this->init();
        $this->request = $request;
    }
    
    protected function getUri()
    {
        return $this->request->uri();
    }

    /**
     * Get params
     *
     * @param string $type
     * @return array
     */
    protected function getParams($type = "")
    {
        switch(strtolower($type)) {
            case 'get':
                $collection = $this->request->paramsGet();
                break;
            case 'post':
                $collection = $this->request->paramsPost();
                break;
            case 'named':
                $collection = $this->request->paramsNamed();
                break;
            default:
                $collection = $this->request->params();
                break;
        }
        return $collection;
    }

    /**
     *
     */
    protected function init()
    {
        $tpl = Di::getDefault()->get('template');
        $md5Email = "";
        if (isset($_SESSION['user'])) {
            try {
                $md5Email = md5($_SESSION['user']->getEmail());
            } catch (Exception $e) {
                ;
            }
        }
        /*
         * Set md5Email for Gravatar
         */
        $tpl->assign("md5Email", $md5Email);
    }

    /**
     * Get routes
     *
     * @return array
     */
    public static function getRoutes()
    {
        $tempo = array();
        $ref = new \ReflectionClass(get_called_class());
        foreach ($ref->getMethods() as $method) {
            $methodName = $method->getName();
            if (substr($methodName, -6) == 'Action') {
                foreach (explode("\n", $method->getDocComment()) as $line) {
                    $str = trim(str_replace("* ", '', $line));
                    if (substr($str, 0, 6) == '@route') {
                        $route = substr($str, 6);
                        $tempo[$methodName]['route'] = trim($route);
                    } elseif (substr($str, 0, 7) == '@method') {
                        $method_type = strtoupper(substr($str, 7));
                        $tempo[$methodName]['method_type'] = trim($method_type);
                    } elseif (substr($str, 0, 4) == '@acl') {
                        $aclFlags = explode(",", trim(substr($str, 4)));
                        $tempo[$methodName]['acl'] = Acl::convertAclFlags($aclFlags);
                    }
                }
            }
        }
        return $tempo;
    }
}
