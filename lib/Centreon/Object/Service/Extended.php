<?php
/*
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

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Service extended information
 *
 * @author sylvestre
 */
class Centreon_Object_Service_Extended extends Centreon_Object
{
    protected $table = "extended_service_information";
    protected $primaryKey = "service_service_id";
    protected $uniqueLabelField = "service_service_id";

    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public function insert($params = array())
    {
        $sql = "INSERT INTO $this->table ";
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        foreach ($params as $key => $value) {
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            $sqlParams[] = $value;
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $this->db->query($sql, $sqlParams);
            return $this->db->lastInsertId($this->table, $this->primaryKey);
        }
        return null;
    }

    /**
     * Get object parameters
     *
     * @param int $objectId
     * @param mixed $parameterNames
     * @return array
     */
    public function getParameters($objectId, $parameterNames)
    {
        $params = parent::getParameters($objectId, $parameterNames);
        $params_image = array("esi_icon_image");
        if (!is_array($params)) {
            return array();
        }
        foreach ($params_image as $image) {
            if (array_key_exists($image, $params)) {
                $sql = "SELECT dir_name, img_path
                        FROM view_img vi
                        LEFT JOIN view_img_dir_relation vidr ON vi.img_id = vidr.img_img_id
                        LEFT JOIN view_img_dir vid ON vid.dir_id = vidr.dir_dir_parent_id
                        WHERE img_id = ?";
                $res = $this->getResult($sql, array($params[$image]), "fetch");
                if (is_array($res)) {
                    $params[$image] = $res["dir_name"]."/".$res["img_path"];
                }
            }
        }
        return $params;
    }

    public function duplicate()
    {
    }
}
