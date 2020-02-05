<?php
/*
 * Copyright 2005-2019 CENTREON
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
    private $procList;
    public $DB;
    public $centreon_DB;
    public $db_prefix;
    public $hostList;
    public $hosttplList;
    public $serviceList;
    public $serviceTplList;
    public $hostIconeList;
    public $diff;
    public $api;

    /**
     * Constructor
     *
     * @param int $retry
     * @param string $db_name
     * @param string $db_user
     * @param string $db_host
     * @param string $db_password
     * @param CentreonDB $pearDB
     * @param string $db_prefix
     */
    public function __construct($pearDB)
    {
        $this->api = new WikiApi();
        $this->centreon_DB = $pearDB;
        $this->hostList = array();
        $this->hosttplList = array();
        $this->serviceList = array();
        $this->serviceTplList = array();
        $this->setProcedures();
    }

    /**
     * Set procedures
     *
     * @return void
     */
    private function setProcedures()
    {
         $pages = $this->api->getAllPages();
        //replace space
        foreach ($pages as $page) {
            $page = str_replace(' ', '_', $page);
            $this->procList[$page] = '';
        }
    }

    /**
     * Get Procedures
     *
     * @return array
     */
    public function getProcedures()
    {
        return $this->procList;
    }

    /**
     * Get Icon List
     *
     * @return array
     */
    public function getIconeList()
    {
        return $this->hostIconeList;
    }

    /**
     *
     */
    public function getDiff($selection, $type = null)
    {
        $wikiContent = $this->getProcedures();
        $diff = array();
        $prefix = "";
        switch ($type) {
            case 0:
                $prefix = "Host_:_";
                break;
            case 1:
                $prefix = "Service_:_";
                break;
            case 2:
                $prefix = "Host-Template_:_";
                break;
            case 3:
                $prefix = "Service-Template_:_";
                break;
        }

        foreach ($selection as $key => $value) {
            if (!isset($wikiContent[$prefix . trim($key)])) {
                $diff[$key] = 0;
            } else {
                $diff[$key] = 1;
            }
        }

        return $diff;
    }

    /**
     * Get Host Id
     *
     * @param string $host_name
     * @param CentreonDB $pearDB
     * @return int
     */
    public function getMyHostID($host_name = null)
    {
        $dbResult = $this->centreon_DB->query(
            "SELECT host_id FROM host " .
            "WHERE host_name = '" . $host_name . "' " .
            "LIMIT 1 "
        );
        $row = $dbResult->fetch();
        if ($row["host_id"]) {
            return $row["host_id"];
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
     * @return void
     */
    public function getMyHostMultipleTemplateModels($host_id = null)
    {
        if (!$host_id) {
            return;
        }

        $tplArr = array();
        $dbResult = $this->centreon_DB->query(
            "SELECT host_tpl_id " .
            "FROM `host_template_relation` " .
            "WHERE host_host_id = '" . $host_id . "' " .
            "ORDER BY `order`"
        );
        while ($row = $dbResult->fetch()) {
            $dbResult2 = $this->centreon_DB->query(
                "SELECT host_name " .
                "FROM host " .
                "WHERE host_id = '" . $row['host_tpl_id'] . "' LIMIT 1"
            );
            $hTpl = $dbResult2->fetch();
            $tplArr[$row['host_tpl_id']] = html_entity_decode($hTpl["host_name"], ENT_QUOTES);
        }
        unset($row);
        unset($hTpl);
        return ($tplArr);
    }

    /**
     * Set host information
     *
     * @return void
     */
    public function setHostInformations()
    {
        /*
         * Get Host Informations
         */
        $dbResult = $this->centreon_DB->query(
            "SELECT host_name, host_id, host_register, ehi_icon_image " .
            "FROM host, extended_host_information ehi " .
            "WHERE host.host_id = ehi.host_host_id " .
            "ORDER BY host_name"
        );
        while ($data = $dbResult->fetch()) {
            if ($data["host_register"] == 1) {
                $this->hostList[$data["host_name"]] = $data["host_id"];
            } else {
                $this->hostTplList[$data["host_name"]] = $data["host_id"];
            }
            $this->hostIconeList["Host_:_" . $data["host_name"]]
                = "./img/media/" . $this->getImageFilePath($data["ehi_icon_image"]);
        }
        $dbResult->closeCursor();
        unset($data);
    }

    /**
     * Get image file path
     *
     * @param int $image_id
     * @return string
     */
    public function getImageFilePath($image_id)
    {
        if (isset($image_id) && $image_id) {
            $dbResult2 = $this->centreon_DB->query(
                "SELECT img_path, dir_alias " .
                "FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                "WHERE vi.img_id = " . $image_id . " " .
                "AND vidr.img_img_id = vi.img_id " .
                "AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1"
            );
            $row2 = $dbResult2->fetch();
            if (isset($row2["dir_alias"])
                && isset($row2["img_path"])
                && $row2["dir_alias"]
                && $row2["img_path"]
            ) {
                return $row2["dir_alias"] . "/" . $row2["img_path"];
            }
            $dbResult2->closeCursor();
            unset($row2);
        } else {
            return "../icones/16x16/server_network.gif";
        }
    }

    /**
     * Set service information
     *
     * @return void
     */
    public function setServiceInformations()
    {
        $dbResult = $this->centreon_DB->query(
            "SELECT service_description, service_id, service_register " .
            "FROM service WHERE service_register = '0' " .
            "ORDER BY service_description"
        );
        while ($data = $dbResult->fetch()) {
            $this->serviceTplList["Service_:_" . $data["service_description"]] = $data["service_id"];
        }
        $dbResult->closeCursor();
        unset($data);
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
