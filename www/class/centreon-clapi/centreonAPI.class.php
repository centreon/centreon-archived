<?php
/**
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

namespace CentreonClapi;

require_once _CENTREON_PATH_ . "www/class/centreon-clapi/centreonExported.class.php";
require_once realpath(dirname(__FILE__) . "/../centreonDB.class.php");
require_once realpath(dirname(__FILE__) . "/../centreonXML.class.php");
require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';
require_once _CENTREON_PATH_ . "www/class/centreonAuth.LDAP.class.php";
require_once _CENTREON_PATH_ . 'www/class/centreonLog.class.php';
require_once __DIR__ . '/centreonUtils.class.php';

if (file_exists(realpath(dirname(__FILE__) . "/../centreonSession.class.php"))) {
    require_once realpath(dirname(__FILE__) . "/../centreonSession.class.php");
} else {
    require_once realpath(dirname(__FILE__) . "/../Session.class.php");
}

/**
 * General Centeon Management
 */
require_once "centreon.Config.Poller.class.php";

/**
 * Declare Centreon API
 *
 */
class CentreonAPI
{
    private static $_instance = null;

    public $dateStart;
    public $login;
    public $password;
    public $action;
    public $object;
    public $options;
    public $args;
    public $DB;
    public $DBC;
    public $DBN;
    public $format;
    public $xmlObj;
    public $debug;
    public $variables;
    public $centreon_path;
    public $optGen;
    private $utilsObject;
    private $return_code;
    private $relationObject;
    private $objectTable;
    private $aExport = array();

    public function __construct($user, $password, $action, $centreon_path, $options)
    {
        global $version;
        global $licensedModule;

        $licensedModule = array();

        /**
         * Set variables
         */
        $this->debug = 0;
        $this->return_code = 0;

        if (isset($user)) {
            $this->login = htmlentities($user, ENT_QUOTES);
        }
        if (isset($password)) {
            $this->password = htmlentities($password, ENT_QUOTES);
        }
        if (isset($action)) {
            $this->action = htmlentities(strtoupper($action), ENT_QUOTES);
        }

        $this->options = $options;
        $this->centreon_path = $centreon_path;

        if (isset($options["v"])) {
            $this->variables = $options["v"];
        } else {
            $this->variables = "";
        }

        if (isset($options["o"])) {
            $this->object = htmlentities(strtoupper($options["o"]), ENT_QUOTES);
        } else {
            $this->object = "";
        }

        $this->objectTable = array();

        /**
         * Centreon DB Connexion
         */
        $this->DB = new \CentreonDB();
        $this->DBC = new \CentreonDB('centstorage');
        $this->dateStart = time();

        $this->utilsObject = new CentreonUtils();

        $this->relationObject = array();
        $this->relationObject["CMD"] = array(
            'module' => 'core',
            'class' => 'Command',
            'export' => true
        );
        $this->relationObject["HOST"] = array(
            'module' => 'core',
            'class' => 'Host',
            'libs' => array(
                'centreonService.class.php',
                'centreonHostGroup.class.php',
                'centreonContact.class.php',
                'centreonContactGroup.class.php'
            ),
            'export' => true
        );
        $this->relationObject["SERVICE"] = array(
            'module' => 'core',
            'class' => 'Service',
            'libs' => array(
                'centreonHost.class.php'
            ),
            'export' => true
        );
        $this->relationObject["HGSERVICE"] = array(
            'module' => 'core',
            'class' => 'HostGroupService',
            'export' => true
        );
        $this->relationObject["VENDOR"] = array(
            'module' => 'core',
            'class' => 'Manufacturer',
            'export' => true
        );
        $this->relationObject["TRAP"] = array(
            'module' => 'core',
            'class' => 'Trap',
            'export' => true
        );
        $this->relationObject["HG"] = array(
            'module' => 'core',
            'class' => 'HostGroup',
            'export' => true
        );
        $this->relationObject["HC"] = array(
            'module' => 'core',
            'class' => 'HostCategory',
            'export' => true
        );
        $this->relationObject["SG"] = array(
            'module' => 'core',
            'class' => 'ServiceGroup',
            'export' => true
        );
        $this->relationObject["SC"] = array(
            'module' => 'core',
            'class' => 'ServiceCategory',
            'export' => true
        );
        $this->relationObject["CONTACT"] = array(
            'module' => 'core',
            'class' => 'Contact',
            'libs' => array(
                'centreonCommand.class.php'
            ),
            'export' => true
        );
        $this->relationObject["LDAP"] = array(
            'module' => 'core',
            'class' => 'LDAP',
            'export' => true
        );
        $this->relationObject["CONTACTTPL"] = array(
            'module' => 'core',
            'class' => 'ContactTemplate',
            'export' => true
        );
        $this->relationObject["CG"] = array(
            'module' => 'core',
            'class' => 'ContactGroup',
            'export' => true
        );
        /* Dependencies */
        $this->relationObject["DEP"] = array(
            'module' => 'core',
            'class' => 'Dependency',
            'export' => true
        );
        /* Downtimes */
        $this->relationObject["DOWNTIME"] = array(
            'module' => 'core',
            'class' => 'Downtime',
            'export' => true
        );
        /* RtDowntimes */
        $this->relationObject["RTDOWNTIME"] = array(
            'module' => 'core',
            'class' => 'RtDowntime',
            'export' => true
        );
        /* Templates */
        $this->relationObject["HTPL"] = array(
            'module' => 'core',
            'class' => 'HostTemplate',
            'export' => true
        );
        $this->relationObject["STPL"] = array(
            'module' => 'core',
            'class' => 'ServiceTemplate',
            'export' => true
        );
        $this->relationObject["TP"] = array(
            'module' => 'core',
            'class' => 'TimePeriod',
            'export' => true
        );
        $this->relationObject["INSTANCE"] = array(
            'module' => 'core',
            'class' => 'Instance',
            'export' => true
        );
        $this->relationObject["ENGINECFG"] = array(
            'module' => 'core',
            'class' => 'EngineCfg',
            'export' => true
        );
        $this->relationObject["CENTBROKERCFG"] = array(
            'module' => 'core',
            'class' => 'CentbrokerCfg',
            'export' => true
        );
        $this->relationObject["RESOURCECFG"] = array(
            'module' => 'core',
            'class' => 'ResourceCfg',
            'export' => true
        );
        $this->relationObject["ACL"] = array(
            'module' => 'core',
            'class' => 'ACL',
            'export' => false
        );
        $this->relationObject["ACLGROUP"] = array(
            'module' => 'core',
            'class' => 'ACLGroup',
            'export' => true
        );
        $this->relationObject["ACLACTION"] = array(
            'module' => 'core',
            'class' => 'ACLAction',
            'export' => true
        );
        $this->relationObject["ACLMENU"] = array(
            'module' => 'core',
            'class' => 'ACLMenu',
            'export' => true
        );
        $this->relationObject["ACLRESOURCE"] = array(
            'module' => 'core',
            'class' => 'ACLResource',
            'export' => true
        );
        $this->relationObject["SETTINGS"] = array(
            'module' => 'core',
            'class' => 'Settings',
            'export' => false
        );

        /* Get objects from modules */
        $objectsPath = array();
        $DBRESULT = $this->DB->query("SELECT name FROM modules_informations");
        while ($row = $DBRESULT->fetchRow()) {

            if ($this->checkModuleValidity($row['name'])) {
                $objectsPath = array_merge(
                    $objectsPath,
                    glob(_CENTREON_PATH_ . 'www/modules/' . $row['name'] . '/centreon-clapi/class/*.php')
                );
            }
        }

        foreach ($objectsPath as $objectPath) {
            if (preg_match('/([\w-]+)\/centreon-clapi\/class\/centreon(\w+).class.php/', $objectPath, $matches)) {
                if (isset($matches[1]) && isset($matches[2])) {
                    $finalNamespace = substr($matches[1], 0, stripos($matches[1], '-server'));

                    $finalNamespace = implode(
                        '',
                        array_map(
                            function ($n) {
                                return ucfirst($n);
                            },
                            explode('-', $finalNamespace)
                        )
                    );
                    $this->relationObject[strtoupper($matches[2])] = array(
                        'module' => $matches[1],
                        'namespace' => $finalNamespace,
                        'class' => $matches[2],
                        'export' => true
                    );
                }
            }
        }

        /*
         * Manage version
         */
        $this->optGen = $this->getOptGen();
        $version = $this->optGen["version"];
        $this->delim = ";";
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function checkModuleValidity($moduleName)
    {
        global $licensedModule;

        $isValid = true;

        $checkLicenseFile = _CENTREON_PATH_ . "www/modules/$moduleName/extensions/checkLicense.php";
        if (file_exists($checkLicenseFile)) {
            require_once $checkLicenseFile;
        }

        if (in_array($moduleName, $licensedModule)) {
            $isValid = false;
            $licenseFile = _CENTREON_PATH_ . "www/modules/$moduleName/license/merethis_lic.zl";

            if (function_exists("zend_loader_file_encoded")) {

                if (file_exists($licenseFile)) {

                    $zend_info = $this->parseZendLicenseFile($licenseFile);

                    $license_expires = strtotime($zend_info['Expires']);
                    if ($license_expires > time()) {
                        $isValid = true;
                    }
                }
            }
        }

        return $isValid;
    }

    /**
     * @param $file
     * @return array
     */
    private function parseZendLicenseFile($file)
    {
        $lines = preg_split('/\n/', file_get_contents($file));
        $infos = array();
        foreach ($lines as $line) {
            if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match)) {
                $infos[$match[1]] = $match[2];
            }
        }
        return $infos;
    }

    /**
     *
     * @param void
     * @return CentreonApi
     */
    public static function getInstance(
        $user = null,
        $password = null,
        $action = null,
        $centreon_path = null,
        $options = null
    )
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new CentreonAPI($user, $password, $action, $centreon_path, $options);
        }

        return self::$_instance;
    }

    /**
     * Set Return Code
     *
     * @param int $returnCode
     * @return void
     */
    public function setReturnCode($returnCode)
    {
        $this->return_code = $returnCode;
    }

    /**
     * Centreon Object Management
     */
    protected function requireLibs($object)
    {
        if ($object != "") {
            if (isset($this->relationObject[$object]['class'])
                && isset($this->relationObject[$object]['module'])
                && !class_exists("\CentreonClapi\Centreon" . $this->relationObject[$object]['class'])
            ) {
                if ($this->relationObject[$object]['module'] == 'core') {
                    require_once "centreon" . $this->relationObject[$object]['class'] . ".class.php";
                } else {
                    require_once _CENTREON_PATH_ . "/www/modules/"
                        . $this->relationObject[$object]['module']
                        . "/centreon-clapi/class/centreon"
                        . $this->relationObject[$object]['class']
                        . ".class.php";
                }
            }

            if (isset($this->relationObject[$object]['libs'])
                && !array_walk($this->relationObject[$object]['libs'], 'class_exists')) {
                array_walk($this->relationObject[$object]['libs'], 'require_once');
            }
        } else {
            foreach ($this->relationObject as $sSynonyme => $oObjet) {
                if (isset($oObjet['class'])
                    && isset($oObjet['module'])
                    && !class_exists("\CentreonClapi\Centreon" . $oObjet['class'])
                ) {
                    if ($oObjet['module'] == 'core') {
                        require_once _CENTREON_PATH_
                            . "www/class/centreon-clapi/centreon"
                            . $oObjet['class'] . ".class.php";
                    } else {
                        require_once _CENTREON_PATH_
                            . "/www/modules/" . $oObjet['module']
                            . "/centreon-clapi/class/centreon"
                            . $oObjet['class'] . ".class.php";
                    }
                }
                if (isset($oObjet['libs']) && !array_walk($oObjet['libs'], 'class_exists')) {
                    array_walk($oObjet['libs'], 'require_once');
                }
            }
        }

        /**
         * Default class needed
         */

        require_once _CLAPI_CLASS_ . "/centreonTimePeriod.class.php";
        require_once _CLAPI_CLASS_ . "/centreonACLResources.class.php";
    }

    /**
     * Get General option of Centreon
     */
    private function getOptGen()
    {
        $DBRESULT = $this->DB->query("SELECT * FROM options");
        while ($row = $DBRESULT->fetchRow()) {
            $this->optGen[$row["key"]] = $row["value"];
        }
        $DBRESULT->free();
    }

    /**
     *
     * Set user login
     * @param varchar $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     *
     * Set password of the user
     * @param varchar $password
     */
    public function setPassword($password)
    {
        $this->password = trim($password);
    }

    /**
     * Check user access and password
     *
     * @param boolean $useSha1
     * @return return bool 1 if user can login
     */
    public function checkUser($useSha1 = false)
    {
        if (!isset($this->login) || $this->login == "") {
            print "ERROR: Can not connect to centreon without login.\n";
            $this->printHelp();
            exit();
        }
        if (!isset($this->password) || $this->password == "") {
            print "ERROR: Can not connect to centreon without password.";
            $this->printHelp();
        }

        /**
         * Check Login / Password
         */
        if ($useSha1) {
            $pass = $this->utilsObject->encodePass($this->password, 'sha1');
        } else {
            $pass = $this->utilsObject->encodePass($this->password, 'md5');
        }
        $DBRESULT = $this->DB->query("SELECT *
                 FROM contact
                 WHERE contact_alias = '" . $this->login . "'
                 AND contact_activate = '1'
                 AND contact_oreon = '1'");
        if ($DBRESULT->numRows()) {
            $row = $DBRESULT->fetchRow();
            if ($row['contact_admin'] == 1) {
                $algo = $this->utilsObject->detectPassPattern($row['contact_passwd']);
                if (!$algo) {
                    if ($useSha1) {
                        $row['contact_passwd'] = 'sha1__' . $row['contact_passwd'];
                    } else {
                        $row['contact_passwd'] = 'md5__' . $row['contact_passwd'];
                    }
                }
                if ($row['contact_passwd'] == $pass) {
                    \CentreonClapi\CentreonUtils::setUserId($row['contact_id']);
                    return 1;
                } elseif ($row['contact_auth_type'] == 'ldap') {
                    $CentreonLog = new \CentreonUserLog(-1, $this->DB);
                    $centreonAuth = new \CentreonAuthLDAP(
                        $this->DB,
                        $CentreonLog,
                        $this->login,
                        $this->password,
                        $row,
                        $row['ar_id']
                    );
                    if ($centreonAuth->checkPassword() == 1) {
                        \CentreonClapi\CentreonUtils::setUserId($row['contact_id']);
                        return 1;
                    }
                }
            } else {
                print "Centreon CLAPI is for admin users only.\n";
                exit(1);
            }
        }
        print "Invalid credentials.\n";
        exit(1);
    }

    /**
     *
     * return (print) a "\n"
     */
    public function endOfLine()
    {
        print "\n";
    }

    /**
     *
     * close the current action
     */
    public function close()
    {
        print "\n";
        exit($this->return_code);
    }

    /**
     * Print usage for using CLAPI ...
     *
     * @param boolean $dbOk | whether db is ok
     * @param int $returnCode
     */
    public function printHelp($dbOk = true, $returnCode = 0)
    {
        if ($dbOk) {
            $this->printLegals();
        }
        print "This software comes with ABSOLUTELY NO WARRANTY. This is free software,\n";
        print "and you are welcome to modify and redistribute it under the GPL license\n\n";
        print "usage: ./centreon -u <LOGIN> -p <PASSWORD> [-s] -o <OBJECT> -a <ACTION> [-v]\n";
        print "  -s     Use SHA1 on password (default is MD5)\n";
        print "  -v     variables \n";
        print "  -h     Print help \n";
        print "  -V     Print version \n";
        print "  -o     Object type \n";
        print "  -a     Launch action on Centreon\n";
        print "     Actions are the followings :\n";
        print "       - POLLERGENERATE: Build nagios configuration for a poller (poller id in -v parameters)\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a POLLERGENERATE -v 1 \n";
        print "       - POLLERTEST: Test nagios configuration for a poller (poller id in -v parameters)\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a POLLERTEST -v 1 \n";
        print "       - CFGMOVE: move nagios configuration for a poller to final directory (poller id in -v parameters)\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a CFGMOVE -v 1 \n";
        print "       - POLLERRESTART: Restart a poller (poller id in -v parameters)\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a POLLERRESTART -v 1 \n";
        print "       - POLLERRELOAD: Reload a poller (poller id in -v parameters)\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a POLLERRELOAD -v 1 \n";
        print "       - POLLERLIST: list all pollers\n";
        print "           #> ./centreon -u <LOGIN> -p <PASSWORD> -a POLLERLIST\n";
        print "\n";
        print "   For more information about configuration objects, please refer to CLAPI wiki:\n";
        print "      - http://documentation.centreon.com/docs/centreon-clapi/ \n";
        print "\n";
        print "Notes:\n";
        print "  - Actions can be written in lowercase chars\n";
        print "  - LOGIN and PASSWORD is an admin account of Centreon\n";
        print "\n";
        exit($returnCode);
    }

    /**
     *
     * Get variable passed in parameters
     * @param varchar $str
     */
    public function getVar($str)
    {
        $res = explode("=", $str);
        return $res[1];
    }

    /**
     *
     * Check that parameters are not empty
     * @param varchar $str
     */
    private function checkParameters($str)
    {
        if (!isset($this->options["v"]) || $this->options["v"] == "") {
            print "No options defined.\n";
            $this->return_code = 1;
            return 1;
        }
    }

    /**
     *
     * Init XML Flow
     */
    public function initXML()
    {
        $this->xmlObj = new CentreonXML();
    }

    /**
     * Main function : Launch action
     *
     * @param boolean $exit If exit or return the return code
     */
    public function launchAction($exit = true)
    {
        $action = strtoupper($this->action);

        /**
         * Debug
         */
        if ($this->debug) {
            print "DEBUG : $action\n";
        }

        /**
         * Check method availability before using it.
         */
        if ($this->object) {
            /**
             * Require needed class
             */
            $this->requireLibs($this->object);

            /**
             * Check class declaration
             */
            if (isset($this->relationObject[$this->object]['class'])) {
                if ($this->relationObject[$this->object]['module'] === 'core') {
                    $objName = "\CentreonClapi\centreon" . $this->relationObject[$this->object]['class'];
                } else {
                    $objName = $this->relationObject[$this->object]['namespace'] .
                        "\CentreonClapi\Centreon" . $this->relationObject[$this->object]['class'];
                }
            } else {
                $objName = "";
            }
            if (!isset($this->relationObject[$this->object]['class']) || !class_exists($objName)) {
                print "Object $this->object not found in Centreon API.\n";
                return 1;
            }
            $obj = new $objName($this->DB, $this->object);
            if (method_exists($obj, $action) || method_exists($obj, "__call")) {
                $this->return_code = $obj->$action($this->variables);
            } else {
                print "Method not implemented into Centreon API.\n";
                return 1;
            }
        } else {
            if (method_exists($this, $action)) {
                $this->return_code = $this->$action();
                print "Return code end : " . $this->return_code . "\n";
            } else {
                print "Method not implemented into Centreon API.\n";
                $this->return_code = 1;
            }
        }
        if ($exit) {
            exit($this->return_code);
        } else {
            return $this->return_code;
        }
    }

    /**
     * Import Scenario file
     */
    public function import($filename)
    {
        $globalReturn = 0;

        $this->fileExists($filename);

        /*
         * Open File in order to read it.
         */
        $handle = fopen($filename, 'r');
        if ($handle) {
            $i = 0;
            while ($string = fgets($handle)) {
                $i++;
                $tab = preg_split('/;/', $string);
                if (strlen(trim($string)) != 0 && !preg_match('/^\{OBJECT_TYPE\}/', $string)) {
                    $this->object = trim($tab[0]);
                    $this->action = trim($tab[1]);
                    $this->variables = trim(substr($string, strlen($tab[0] . ";" . $tab[1] . ";")));
                    if ($this->debug == 1) {
                        print "Object : " . $this->object . "\n";
                        print "Action : " . $this->action . "\n";
                        print "VARIABLES : " . $this->variables . "\n\n";
                    }
                    try {
                        $this->launchActionForImport();
                    } catch (CentreonClapiException $e) {
                        echo "Line $i : " . $e->getMessage() . "\n";
                    } catch (Exception $e) {
                        echo "Line $i : " . $e->getMessage() . "\n";
                    }
                    if ($this->return_code) {
                        $globalReturn = 1;
                    }
                }
            }
            fclose($handle);
        }
        return $globalReturn;
    }

    public function launchActionForImport()
    {
        $action = strtoupper($this->action);
        /**
         * Debug
         */
        if ($this->debug) {
            print "DEBUG : $action\n";
        }

        /**
         * Check method availability before using it.
         */
        if ($this->object) {
            $this->iniObject($this->object);

            /**
             * Check class declaration
             */
            $obj = $this->objectTable[$this->object];
            if (method_exists($obj, $action) || method_exists($obj, "__call")) {
                $this->return_code = $obj->$action($this->variables);
            } else {
                print "Method not implemented into Centreon API.\n";
                return 1;
            }
        } else {
            if (method_exists($this, $action) || method_exists($this, "__call")) {
                $this->return_code = $this->$action();
            } else {
                print "Method not implemented into Centreon API.\n";
                $this->return_code = 1;
            }
        }
    }

    /**
     * @param $newOption
     */
    public function setOption($newOption)
    {
        $this->options = $newOption;
    }

    /**
     * Export All configuration
     */
    public function export()
    {
        $this->requireLibs("");

        $this->sortClassExport();

        $this->initAllObjects();

        if (isset($this->options['select'])) {
            CentreonExported::getInstance()->set_filter(1);
            CentreonExported::getInstance()->set_options($this->options);
            $selected = $this->options['select'];
            if (!is_array($this->options['select'])) {
                $selected = array($this->options['select']);
            }
            foreach ($selected as $select) {
                $splits = explode(';', $select);
                if (!isset($this->objectTable[$splits[0]])) {
                    print "Unknown object : $splits[0]\n";
                    $this->setReturnCode(1);
                    $this->close();
                } elseif (!is_null($splits[1]) && $this->objectTable[$splits[0]]->getObjectId($splits[1]) == 0) {
                    echo "Unknown object : $splits[0];$splits[1]\n";
                    $this->setReturnCode(1);
                    $this->close();
                } else {
                    $this->objectTable[$splits[0]]->export_filter(
                        $splits[0],
                        $this->objectTable[$splits[0]]->getObjectId($splits[1]),
                        $splits[1]
                    );
                }
            }
            return $this->return_code;
        } else {
            // header
            echo "{OBJECT_TYPE}{$this->delim}{COMMAND}{$this->delim}{PARAMETERS}\n";
            if (count($this->aExport) > 0) {
                foreach ($this->aExport as $oObjet) {
                    if (method_exists($this->objectTable[$oObjet], 'export')) {
                        $this->objectTable[$oObjet]->export();
                    }
                }
            }
        }
    }

    /**
     *
     * Init an object
     * @param unknown_type $DB
     * @param unknown_type $objname
     */
    private function iniObject($objname)
    {
        $className = '';
        if (isset($this->relationObject[$objname]['namespace'])
            && $this->relationObject[$objname]['namespace']) {
            $className .= '\\' . $this->relationObject[$objname]['namespace'];
        }
        $className .= '\CentreonClapi\centreon' . $this->relationObject[$objname]['class'];
        $this->requireLibs($objname);
        $this->objectTable[$objname] = new $className($this->DB, $objname);
    }

    /**
     * Init All object instance in order to export all informations
     */
    private function initAllObjects()
    {
        if (count($this->aExport) > 0) {
            foreach ($this->aExport as $oObjet) {
                $this->iniObject($oObjet);
            }
        }
    }

    /**
     * Check if file exists
     */
    private function fileExists($filename)
    {
        if (!file_exists($filename)) {
            print "$filename : File doesn't exists\n";
            exit(1);
        }
    }

    /**
     *
     * Print centreon version and legal use
     */
    public function printLegals()
    {
        $DBRESULT = &$this->DB->query("SELECT * FROM informations WHERE `key` = 'version'");
        $data = &$DBRESULT->fetchRow();
        print "Centreon version " . $data["value"] . " - ";
        print "Copyright Centreon - www.centreon.com\n";
        unset($data);
    }

    /**
     *
     * Print centreon version
     */
    public function printVersion()
    {
        $res = $this->DB->query("SELECT * FROM informations WHERE `key` = 'version'");
        $data = $res->fetchRow();
        print "Centreon version " . $data["value"] . "\n";
        $res = $this->DB->query("SELECT mod_release FROM modules_informations WHERE name = 'centreon-clapi'");
        $clapiVersion = 'undefined';
        if ($res->numRows()) {
            $data = $res->fetchRow();
            $clapiVersion = $data['mod_release'];
        }
        print "Centreon CLAPI version " . $clapiVersion . "\n";
    }

    /**     * *****************************************************
     *
     * API Possibilities
     */

    /**
     *
     * List all poller declared in Centreon
     */
    public function POLLERLIST()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->getPollerList($this->format);
    }

    /**
     *
     * Launch poller restart
     */
    public function POLLERRESTART()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->pollerRestart($this->variables);
    }

    /**
     *
     * Launch poller reload
     */
    public function POLLERRELOAD()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->pollerReload($this->variables);
    }

    /**
     *
     * Launch poller configuration files generation
     */
    public function POLLERGENERATE()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->pollerGenerate($this->variables, $this->login, $this->password);
    }

    /**
     *
     * Launch poller configuration test
     */
    public function POLLERTEST()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->pollerTest($this->format, $this->variables);
    }

    /**
     * Execute the post generation command
     */
    public function POLLEREXECCMD()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->execCmd($this->variables);
    }

    /**
     *
     * move configuration files into final directory
     */
    public function CFGMOVE()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->cfgMove($this->variables);
    }

    /**
     * Send trap configuration file to poller
     */
    public function SENDTRAPCFG()
    {
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        return $poller->sendTrapCfg($this->variables);
    }

    /**
     *
     * Apply configuration Generation + move + reload
     */
    public function APPLYCFG()
    {
        /**
         * Display time for logs
         */
        print date("Y-m-d H:i:s") . " - APPLYCFG\n";

        /**
         * Launch Actions
         */
        $poller = new CentreonConfigPoller($this->DB, $this->centreon_path, $this->DBC);
        $this->return_code = $poller->pollerGenerate($this->variables, $this->login, $this->password);
        $this->endOfLine();
        if ($this->return_code == 0) {
            $this->return_code = $poller->pollerTest($this->format, $this->variables);
            $this->endOfLine();
        }
        if ($this->return_code == 0) {
            $this->return_code = $poller->cfgMove($this->variables);
            $this->endOfLine();
        }
        if ($this->return_code == 0) {
            $this->return_code = $poller->pollerReload($this->variables);
        }
        if ($this->return_code == 0) {
            $this->return_code = $poller->execCmd($this->variables);
        }
        return $this->return_code;
    }

    /**
     * This method sort the objects to export
     */
    public function sortClassExport()
    {

        if (isset($this->relationObject) && is_array(($this->relationObject))) {
            $aObject = $this->relationObject;
            while ($oObjet = array_slice($aObject, -1, 1, true)) {
                $key = key($oObjet);
                if (isset($oObjet[$key]['class'])
                    && $oObjet[$key]['export'] === true
                    && !in_array($key, $this->aExport)) {
                    $objName = '';
                    if (isset($oObjet[$key]['namespace'])) {
                        $objName = '\\' . $oObjet[$key]['namespace'];
                    }

                    $objName .= '\CentreonClapi\Centreon' . $oObjet[$key]['class'];
                    $objVars = get_class_vars($objName);

                    if (isset($objVars['aDepends'])) {
                        $bInsert = true;
                        foreach ($objVars['aDepends'] as $item => $oDependence) {
                            $keyDep = strtoupper($oDependence);
                            if (!in_array($keyDep, $this->aExport)) {
                                $bInsert = false;
                            }
                        }

                        if ($bInsert) {
                            $this->aExport[] = $key;
                            array_pop($aObject);
                        } else {
                            $aObject = array_merge($oObjet, $aObject);
                        }
                    } else {
                        $this->aExport[] = $key;
                        array_pop($aObject);
                    }
                } else {
                    array_pop($aObject);
                }
            }
        }
    }
}
