<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once dirname(__FILE__) . '/centreonUser.class.php';
require_once dirname(__FILE__) . '/centreonGMT.class.php';
require_once dirname(__FILE__) . '/centreonLogAction.class.php';
require_once dirname(__FILE__) . '/centreonExternalCommand.class.php';
require_once dirname(__FILE__) . '/centreonBroker.class.php';
require_once dirname(__FILE__) . '/centreonHostgroups.class.php';
require_once realpath(dirname(__FILE__) . "/centreonDBInstance.class.php");

/**
 * Class for load application Centreon
 */
class Centreon
{
    public $Nagioscfg;
    public $optGen;
    public $informations;
    public $redirectTo;
    public $modules;
    public $hooks;

    /*
     * @var array : saved user's pagination filter value
     */
    public $historyPage;

    /*
     * @var string : saved last page's file name
     */
    public $historyLastUrl;

    /*
     * @var array : saved user's filters
     */
    public $historySearch;

    public $historySearchService;
    public $historySearchOutput;
    public $historyLimit;
    public $search_type_service;
    public $search_type_host;
    public $poller;
    public $template;
    public $hostgroup;
    public $host_id;
    public $host_group_search;
    public $host_list_search;

    /**
     * @var \CentreonUser
     */
    public $user;
    public $CentreonGMT;
    public $CentreonLogAction;
    public $extCmd;

    /**
     * Class constructor
     *
     * @param object $user User objects
     */
    public function __construct($userInfos)
    {
        global $pearDB;

        /*
         * Get User informations
         */
        $this->user = new CentreonUser($userInfos);

        /*
         * Get Local nagios.cfg file
         */
        $this->initNagiosCFG();

        /*
         * Get general options
         */
        $this->initOptGen();

        /*
         * Get general informations
         */
        $this->initInformations();

        /*
         * Grab Modules
         */
        $this->creatModuleList();

        /*
         * Grab Hooks
         */
        $this->initHooks();

        /*
         * Create GMT object
         */
        $this->CentreonGMT = new CentreonGMT($pearDB);

        /*
         * Create LogAction object
         */
        $this->CentreonLogAction = new CentreonLogAction($this->user);

        /*
         * Init Poller id
         */
        $this->poller = 0;

        /*
         * Init External CMD object
         */
        $this->extCmd = new CentreonExternalCommand();
    }

    /**
     * Create a list of all module installed into Centreon
     *
     * @param $pearDB The database connection to centreon database
     */
    public function creatModuleList()
    {
        $this->modules = array();
        $query = "SELECT `name` FROM `modules_informations`";
        $dbResult = CentreonDBInstance::getConfInstance()->query($query);
        while ($result = $dbResult->fetch()) {
            $this->modules[$result["name"]] = array(
                "name" => $result["name"],
                "gen" => false,
                "restart" => false,
                "license" => false
            );

            if (is_dir("./modules/" . $result["name"] . "/generate_files/")) {
                $this->modules[$result["name"]]["gen"] = true;
            }
            if (is_dir("./modules/" . $result["name"] . "/restart_pollers/")) {
                $this->modules[$result["name"]]["restart"] = true;
            }
            if (is_dir("./modules/" . $result["name"] . "/restart_pollers/")) {
                $this->modules[$result["name"]]["restart"] = true;
            }
            if (file_exists("./modules/" . $result["name"] . "/license/merethis_lic.zl")) {
                $this->modules[$result["name"]]["license"] = true;
            }
        }
        $dbResult = null;
    }

    public function initHooks()
    {
        $this->hooks = array();

        foreach ($this->modules as $name => $parameters) {
            $hookPaths = glob(_CENTREON_PATH_ . '/www/modules/' . $name . '/hooks/*.class.php');
            foreach ($hookPaths as $hookPath) {
                if (preg_match('/\/([^\/]+?)\.class\.php$/', $hookPath, $matches)) {
                    require_once($hookPath);
                    $explodedClassName = explode('_', $matches[1]);
                    $className = '';
                    foreach ($explodedClassName as $partClassName) {
                        $className .= ucfirst(strtolower($partClassName));
                    }
                    if (class_exists($className)) {
                        $hookName = '';
                        for ($i = 1; $i < count($explodedClassName); $i++) {
                            $hookName .= ucfirst(strtolower($explodedClassName[$i]));
                        }
                        $hookMethods = get_class_methods($className);
                        foreach ($hookMethods as $hookMethod) {
                            $this->hooks[$hookName][$hookMethod][] = array(
                                'path' => $hookPath,
                                'class' => $className
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Create history list
     */
    public function createHistory()
    {
        $this->historyPage = array();
        $this->historyLastUrl = '';
        $this->historySearch = array();
        $this->historySearchService = array();
        $this->historySearchOutput = array();
        $this->historyLimit = array();
        $this->search_type_service = 1;
        $this->search_type_host = 1;
    }

    /**
     * Initiate nagios option list
     *
     * @param $pearDB The database connection to centreon database
     */
    public function initNagiosCFG()
    {
        $this->Nagioscfg = array();
        /*
         * We don't check activate because we can a server without a engine on localhost running
         * (but we order to get if we have one)
         */
        $DBRESULT = CentreonDBInstance::getConfInstance()->query(
            "SELECT illegal_object_name_chars, cfg_dir FROM cfg_nagios, nagios_server
            WHERE nagios_server.id = cfg_nagios.nagios_server_id
            AND nagios_server.localhost = '1'
            ORDER BY cfg_nagios.nagios_activate
            DESC LIMIT 1"
        );
        $this->Nagioscfg = $DBRESULT->fetch();
        $DBRESULT = null;
    }

    /**
     * Initiate general option list
     *
     * @param $pearDB The database connection to centreon database
     */
    public function initOptGen()
    {
        $this->optGen = array();
        $DBRESULT = CentreonDBInstance::getConfInstance()->query("SELECT * FROM `options`");
        while ($opt = $DBRESULT->fetch()) {
            $this->optGen[$opt["key"]] = $opt["value"];
        }
        $DBRESULT = null;
        unset($opt);
    }

    /**
     * Store centreon informations in session
     *
     * @return void
     */
    public function initInformations(): void
    {
        $this->informations = [];
        $result = CentreonDBInstance::getConfInstance()->query("SELECT * FROM `informations`");
        while ($row = $result->fetch()) {
            $this->informations[$row["key"]] = $row["value"];
        }
    }

    /**
     * Check illegal char defined into nagios.cfg file
     *
     * @param string $name The string to sanitize
     * @return string The string sanitized
     */
    public function checkIllegalChar($name)
    {
        $DBRESULT = CentreonDBInstance::getConfInstance()->query("SELECT illegal_object_name_chars FROM cfg_nagios");
        while ($data = $DBRESULT->fetchColumn()) {
            $tab = str_split(html_entity_decode($data, ENT_QUOTES, "UTF-8"));
            foreach ($tab as $char) {
                $name = str_replace($char, "", $name);
            }
        }
        $DBRESULT = null;
        return $name;
    }
}
