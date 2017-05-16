<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";

class CentreonWebService
{
    /**
     * @var CentreonDB|null
     */
    protected $pearDB = null;

    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @var null
     */
    protected $token = null;

    /**
     * @var
     */
    protected static $webServicePaths;

    /**
     * CentreonWebService constructor.
     */
    public function __construct()
    {
        if (isset($this->pearDB)) {
            $this->pearDB = $this->pearDB;
        } else {
            $this->pearDB = new CentreonDB();
        }
        $this->loadArguments();
        $this->loadToken();
    }

    /**
     * Load arguments compared http method
     */
    protected function loadArguments()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $httpParams = $_GET;
                unset($httpParams['action']);
                unset($httpParams['object']);
                $this->arguments = $httpParams;
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $this->arguments = $this->parseBody();
                break;
            case 'DELETE':
                break;
            default:
                static::sendJson("Bad request", 400);
                break;
        }
    }

    /**
     * Parse the body for get arguments
     * The body must be JSON format
     * @return array
     */
    protected function parseBody()
    {
        try {
            $httpParams = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $e) {
            static::sendJson("Bad parameters", 400);
        }
        return $httpParams;
    }

    /**
     * Load the token for class if exists
     */
    protected function loadToken()
    {
        if (isset($_SERVER['HTTP_CENTREON_AUTH_TOKEN'])) {
            $this->token = $_SERVER['HTTP_CENTREON_AUTH_TOKEN'];
        }
    }

    /**
     * Get webservice
     *
     * @param string $object
     * @return type
     */
    protected static function webservicePath($object = "")
    {
        $webServiceClass = array();
        foreach (self::$webServicePaths as $webServicePath) {
            if (false !== strpos($webServicePath, $object . '.class.php')) {
                require_once $webServicePath;
                $explodedClassName = explode('_', $object);
                $className = "";
                foreach ($explodedClassName as $partClassName) {
                    $className .= ucfirst(strtolower($partClassName));
                }
                if (class_exists($className)) {
                    $webServiceClass = array(
                        'path' => $webServicePath,
                        'class' => $className
                    );
                }
            }
        }

        if (count($webServiceClass) === 0) {
            static::sendJson("Method not found", 404);
        }

        return $webServiceClass;
    }

    /**
     * Send json return
     *
     * @param mixed $data The values
     * @param integer $code The HTTP code
     */
    public static function sendJson($data, $code = 200)
    {
        switch ($code) {
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            case 502:
                header("HTTP/1.1 502 Bad Gateway");
                break;
            case 503:
                header("HTTP/1.1 503 Service Unavailable");
                break;
            case 504:
                header("HTTP/1.1 504 Gateway Time-out");
                break;
            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;
            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;
            case 403:
                header("HTTP/1.1 403 Forbidden");
                break;
            case 404:
                header("HTTP/1.1 404 Object not found");
                break;
            case 405:
                header("HTTP/1.1 405 Method not allowed");
                break;
            case 409:
                header("HTTP/1.1 409 Conflict");
                break;
        }
        header('Content-type: application/json');
        print json_encode($data);
        exit();
    }

    /**
     * Update the ttl for a token if the authentication is by token
     */
    protected static function updateTokenTtl()
    {
        global $pearDB;
        if (isset($_SERVER['HTTP_CENTREON_AUTH_TOKEN'])) {
            $query = 'UPDATE ws_token SET generate_date = NOW() WHERE token = ?';
            try {
                $stmt = $pearDB->prepare($query);
                $pearDB->execute($stmt, array((string)$_SERVER['HTTP_CENTREON_AUTH_TOKEN']));
            } catch (Exception $e) {
                static::sendJson("Internal error", 500);
            }
        }
    }

    /**
     * Route the webservice to the good method
     * @global string _CENTREON_PATH_
     * @global type $pearDB3
     */
    public static function router()
    {
        global $pearDB;

        /* Test if route is defined */
        if (false === isset($_GET['object']) || false === isset($_GET['action'])) {
            static::sendJson("Bad parameters", 400);
        }

        $methodPrefix = strtolower($_SERVER['REQUEST_METHOD']);
        $object = $_GET['object'];
        $action = $methodPrefix . ucfirst($_GET['action']);

        /* Generate path for WebService */
        self::$webServicePaths = glob(_CENTREON_PATH_ . '/www/api/class/*.class.php');
        $res = $pearDB->query("SELECT name FROM modules_informations");
        while ($row = $res->fetchRow()) {
            self::$webServicePaths = array_merge(
                self::$webServicePaths,
                glob(_CENTREON_PATH_ . '/www/modules/' . $row['name'] . '/webServices/rest/*.class.php')
            );
        }
        self::$webServicePaths = array_merge(
            self::$webServicePaths,
            glob(_CENTREON_PATH_ . '/www/widgets/*/webServices/rest/*.class.php')
        );

        $webService = self::webservicePath($object);

        /* Initialize the webservice */
        require_once($webService['path']);

        $wsObj = new $webService['class']();

        if (false === method_exists($wsObj, $action)) {
            static::sendJson("Method not found", 404);
        }

        /* Execute the action */
        try {
            static::updateTokenTtl();
            $data = $wsObj->$action();
            $wsObj::sendJson($data);
        } catch (RestException $e) {
            $wsObj::sendJson($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $wsObj::sendJson($e->getMessage(), 500);
        }
    }
}
