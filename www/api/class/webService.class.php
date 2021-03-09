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

if (!(class_exists('centreonDB') || class_exists('\\centreonDB')) && defined('_CENTREON_PATH_')) {
    require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
}

use Centreon\Infrastructure\Webservice\WebserviceAutorizePublicInterface;
use Centreon\Infrastructure\Webservice\WebserviceAutorizeRestApiInterface;

class CentreonWebService
{
    const RESULT_HTML = 'html';
    const RESULT_JSON = 'json';

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
        $this->loadDb();
        $this->loadArguments();
        $this->loadToken();
    }

    /**
     * Load database
     */
    protected function loadDb()
    {
        if (isset($this->pearDB)) {
            $this->pearDB = $this->pearDB;
        } else {
            $this->pearDB = new CentreonDB();
        }
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
                static::sendResult("Bad request", 400);
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
            static::sendResult("Bad parameters", 400);
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
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param boolean $isInternal If the api is call in internal
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if ($isInternal || ($user && $user->admin)) {
            return true;
        }

        return false;
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
            static::sendResult("Method not found", 404);
        }

        return $webServiceClass;
    }

    /**
     * Send json return
     *
     * @param mixed $data The values
     * @param integer $code The HTTP code
     */
    public static function sendResult($data, $code = 200, $format = null)
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
            case 206:
                header("HTTP/1.1 206 Partial content");
                $data = json_decode($data, true);
                break;
        }

        switch ($format) {
            case static::RESULT_HTML:
                header('Content-type: text/html');
                print $data;
                break;
            case static::RESULT_JSON:
            case null:
                header('Content-type: application/json;charset=utf-8');
                print json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
        }

        exit();
    }

    /**
     * Update the ttl for a token if the authentication is by token
     */
    protected static function updateTokenTtl()
    {
        global $pearDB;

        if (isset($_SERVER['HTTP_CENTREON_AUTH_TOKEN'])) {
            try {
                $stmt = $pearDB->prepare(
                    'UPDATE security_token
                    SET expiration_date = (
                        SELECT UNIX_TIMESTAMP(NOW() + INTERVAL (`value` * 60) SECOND)
                        FROM `options`
                        wHERE `key` = \'session_expire\'
                    )
                    WHERE token = :token'
                );
                $stmt->bindValue(':token', $_SERVER['HTTP_CENTREON_AUTH_TOKEN'], \PDO::PARAM_STR);
                $stmt->execute();
            } catch (Exception $e) {
                static::sendResult("Internal error", 500);
            }
        }
    }

    /**
     * Route the webservice to the good method
     * @global string _CENTREON_PATH_
     * @global type $pearDB3
     *
     * @param \Pimple\Container $dependencyInjector
     * @param CentreonUser $user The current user
     * @param boolean $isInternal If the Rest API call is internal
     */
    public static function router(\Pimple\Container $dependencyInjector, $user, $isInternal = false)
    {
        global $pearDB;

        /* Test if route is defined */
        if (false === isset($_GET['object']) || false === isset($_GET['action'])) {
            static::sendResult("Bad parameters", 400);
        }

        $resultFormat = 'json';
        if (isset($_GET['resultFormat'])) {
            $resultFormat = $_GET['resultFormat'];
        }

        $methodPrefix = strtolower($_SERVER['REQUEST_METHOD']);
        $object = $_GET['object'];
        $action = $methodPrefix . ucfirst($_GET['action']);

        /* Generate path for WebService */
        self::$webServicePaths = glob(_CENTREON_PATH_ . '/www/api/class/*.class.php');
        $res = $pearDB->query('SELECT name FROM modules_informations');
        while ($row = $res->fetch()) {
            self::$webServicePaths = array_merge(
                self::$webServicePaths,
                glob(_CENTREON_PATH_ . '/www/modules/' . $row['name'] . '/webServices/rest/*.class.php')
            );
        }
        self::$webServicePaths = array_merge(
            self::$webServicePaths,
            glob(_CENTREON_PATH_ . '/www/widgets/*/webServices/rest/*.class.php')
        );

        $isService = $dependencyInjector['centreon.webservice']->has($object);

        if ($isService === true) {
            $webService = [
                'class' => $dependencyInjector['centreon.webservice']->get($object)
            ];

            // Initialize the language translator
            $dependencyInjector['translator'];

            // Use the web service if has been initialized or initialize it
            if (isset($dependencyInjector[$webService['class']])) {
                $wsObj = $dependencyInjector[$webService['class']];
            } else {
                $wsObj = new $webService['class']();
                $wsObj->setDi($dependencyInjector);
            }
        } else {
            $webService = self::webservicePath($object);

            /**
             * Either we retrieve an instance of this web service that has been
             * created in the dependency injector, or we create a new one.
             */
            if (isset($dependencyInjector[$webService['class']])) {
                $wsObj = $dependencyInjector[$webService['class']];
            } else {
                /* Initialize the webservice */
                require_once($webService['path']);
                $wsObj = new $webService['class']();
            }
        }

        if ($wsObj instanceof CentreonWebServiceDiInterface) {
            $wsObj->finalConstruct($dependencyInjector);
        }

        if (false === method_exists($wsObj, $action)) {
            static::sendResult("Method not found", 404);
        }

        /* Execute the action */
        try {
            if (!static::isWebserviceAllowed($wsObj, $action, $user, $isInternal)) {
                static::sendResult('Forbidden', 403, static::RESULT_JSON);
            }

            static::updateTokenTtl();
            $data = $wsObj->$action();
            $wsObj::sendResult($data, 200, $resultFormat);
        } catch (RestException $e) {
            $wsObj::sendResult($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $wsObj::sendResult($e->getMessage(), 500);
        }
    }

    /**
     * Check webservice authorization
     *
     * @param WebserviceAutorizePublicInterface|WebserviceAutorizeRestApiInterface $webservice
     * @param string $action The action name
     * @param CentreonUser|null $user The current user
     * @param boolean $isInternal If the api is call from internal
     * @return boolean if the webservice is allowed for the current user
     */
    private static function isWebserviceAllowed($webservice, $action, $user, $isInternal): bool
    {
        $allowed = false;

        // skip checks if public interface is implemented
        if ($webservice instanceof WebserviceAutorizePublicInterface) {
            $allowed = true;
        } else {
            $allowed = $webservice->authorize($action, $user, $isInternal);
        }

        return $allowed;
    }
}
