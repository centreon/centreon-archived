<?php

/*
 * Copyright 2005-2020 CENTREON
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

define("PROCEDURE_SIMPLE_MODE", 0);
define("PROCEDURE_INHERITANCE_MODE", 1);
require_once _CENTREON_PATH_ . "/www/class/centreon-knowledge/wikiApi.class.php";

class procedures
{
    private $procList = [];
    public $DB;
    public $centreon_DB;
    public $api;

    /**
     * Constructor
     *
     * @param CentreonDB $pearDB
     */
    public function __construct($pearDB)
    {
        $this->api = new WikiApi();
        $this->centreon_DB = $pearDB;
    }

    /**
     * Set procedures
     *
     * @return void
     */
    public function fetchProcedures()
    {
        if (!empty($this->procList)) {
            return null;
        }

        $pages = $this->api->getAllPages();
        //replace space
        foreach ($pages as $page) {
            $page = str_replace(' ', '_', $page);
            $this->procList[$page] = '';
        }
    }

    /**
     * Get service template
     *
     * @param int $service_id
     * @return array
     */
    public function getMyServiceTemplateModels($service_id = null)
    {
        $tplArr = array();

        $dbResult = $this->centreon_DB->query(
            "SELECT service_description, service_template_model_stm_id " .
            "FROM service " .
            "WHERE service_id = '" . $service_id . "' LIMIT 1"
        );
        $row = $dbResult->fetch();
        if (isset($row['service_template_model_stm_id']) && $row['service_template_model_stm_id'] != "") {
            $dbResult->closeCursor();
            $service_id = $row["service_template_model_stm_id"];
            if ($row["service_description"]) {
                $tplArr[$service_id] = html_entity_decode($row["service_description"], ENT_QUOTES);
            }
            while (1) {
                $dbResult = $this->centreon_DB->query(
                    "SELECT service_description, service_template_model_stm_id " .
                    "FROM service " .
                    "WHERE service_id = '" . $service_id . "' LIMIT 1"
                );
                $row = $dbResult->fetch();
                $dbResult->closeCursor();
                if ($row["service_description"]) {
                    $tplArr[$service_id] = html_entity_decode($row["service_description"], ENT_QUOTES);
                } else {
                    break;
                }
                if ($row["service_template_model_stm_id"]) {
                    $service_id = $row["service_template_model_stm_id"];
                } else {
                    break;
                }
            }
        }
        return ($tplArr);
    }

    /**
     * Get host template models
     *
     * @param int $host_id
     * @return array
     */
    public function getMyHostMultipleTemplateModels($host_id = null)
    {
        if (!$host_id) {
            return [];
        }

        $tplArr = array();
        $dbResult = $this->centreon_DB->query(
            "SELECT host_tpl_id " .
            "FROM `host_template_relation` " .
            "WHERE host_host_id = '" . $host_id . "' " .
            "ORDER BY `order`"
        );
        $statement = $this->centreon_DB->prepare(
            "SELECT host_name " .
            "FROM host " .
            "WHERE host_id = :host_id LIMIT 1"
        );
        while ($row = $dbResult->fetch()) {
            $statement->bindValue(':host_id', $row['host_tpl_id'], \PDO::PARAM_INT);
            $statement->execute();
            $hTpl = $statement->fetch(\PDO::FETCH_ASSOC);
            $tplArr[$row['host_tpl_id']] = html_entity_decode($hTpl["host_name"], ENT_QUOTES);
        }
        unset($row);
        unset($hTpl);
        return $tplArr;
    }

    /**
     * Check if Service has procedure
     *
     * @param string $key
     * @param array $templates
     * @param int $mode
     * @return bool
     */
    public function serviceHasProcedure($key, $templates = array(), $mode = PROCEDURE_SIMPLE_MODE)
    {
        if (isset($this->procList["Service_:_" . $key])) {
            return true;
        }
        if ($mode == PROCEDURE_SIMPLE_MODE) {
            return false;
        } elseif ($mode == PROCEDURE_INHERITANCE_MODE) {
            foreach ($templates as $templateId => $templateName) {
                $res = $this->serviceTemplateHasProcedure($templateName, null, PROCEDURE_SIMPLE_MODE);
                if ($res == true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if Host has procedure
     *
     * @param string $key
     * @param array $templates
     * @param int $mode
     * @return bool
     */
    public function hostHasProcedure($key, $templates = array(), $mode = PROCEDURE_SIMPLE_MODE)
    {
        if (isset($this->procList["Host_:_" . $key])) {
            return true;
        }

        if ($mode == PROCEDURE_SIMPLE_MODE) {
            return false;
        } elseif ($mode == PROCEDURE_INHERITANCE_MODE) {
            foreach ($templates as $templateId => $templateName) {
                $res = $this->hostTemplateHasProcedure($templateName, null, PROCEDURE_SIMPLE_MODE);
                if ($res == true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if Service template has procedure
     *
     * @param string $key
     * @param array $templates
     * @param int $mode
     * @return bool
     */
    public function serviceTemplateHasProcedure($key = "", $templates = array(), $mode = PROCEDURE_SIMPLE_MODE)
    {
        if (isset($this->procList["Service-Template_:_" . $key])) {
            return true;
        }
        if ($mode == PROCEDURE_SIMPLE_MODE) {
            return false;
        } elseif ($mode == PROCEDURE_INHERITANCE_MODE) {
            foreach ($templates as $templateId => $templateName) {
                if (isset($this->procList['Service-Template_:_' . $templateName])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if Host template has procedures
     *
     * @param string $key
     * @param array $templates
     * @return bool
     */
    public function hostTemplateHasProcedure($key = "", $templates = array(), $mode = PROCEDURE_SIMPLE_MODE)
    {
        if (isset($this->procList["Host-Template_:_" . $key])) {
            return true;
        }
        if ($mode == PROCEDURE_SIMPLE_MODE) {
            return false;
        } elseif ($mode == PROCEDURE_INHERITANCE_MODE) {
            foreach ($templates as $templateId => $templateName) {
                if (isset($this->procList['Host-Template_:_' . $templateName])) {
                    return true;
                }
            }
        }
        return false;
    }
}
