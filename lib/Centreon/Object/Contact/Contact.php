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
 * Used for interacting with Contact objects
 *
 * @author sylvestre
 */
class Centreon_Object_Contact extends Centreon_Object
{
    protected $table = "contact";
    protected $primaryKey = "contact_id";
    protected $uniqueLabelField = "contact_alias";

    /**
     * Used for inserting contact into database
     *
     * @param array $params
     * @return int
     */
    public function insert($params = [])
    {
        $sql = "INSERT INTO $this->table ";
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = [];

        // Store password value and remove it from the array to not inserting it in contact table.
        if (isset($params['contact_passwd'])) {
            $password = $params['contact_passwd'];
            unset($params['contact_passwd']);
        }
        foreach ($params as $key => $value) {
            if ($key == $this->primaryKey) {
                continue;
            }
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            $sqlParams[] = trim($value);
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(" . $sqlFields . ") VALUES (" . $sqlValues . ")";
            $this->db->query($sql, $sqlParams);
            $contactId = $this->db->lastInsertId();
            if (isset($password) && isset($contactId)) {
                $statement = $this->db->prepare(
                    "INSERT INTO `contact_password` (password, contact_id, creation_date)
                    VALUES (:password, :contactId, :creationDate)"
                );
                $statement->bindValue(':password', $password, \PDO::PARAM_STR);
                $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
                $statement->bindValue(':creationDate', time(), \PDO::PARAM_INT);
                $statement->execute();
            }
            return $contactId;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        if ($filterType != "OR" && $filterType != "AND") {
            throw new Exception('Unknown filter type');
        }

        if (is_array($parameterNames)) {
            if (($key = array_search('contact_id', $parameterNames)) !== false) {
                $parameterNames[$key] = $this->table . '.contact_id';
            }
            $params = implode(",", $parameterNames);
        } elseif ($parameterNames === "contact_id") {
            $params = $this->table . '.contact_id';
        } else {
            $params = $parameterNames;
        }
        $sql = "SELECT $params, cp.password AS contact_passwd FROM $this->table " .
        "LEFT JOIN contact_password cp ON cp.contact_id = contact.contact_id";
        $filterTab = array();
        if (count($filters)) {
            foreach ($filters as $key => $rawvalue) {
                if (!count($filterTab)) {
                    $sql .= " WHERE $key ";
                } else {
                    $sql .= " $filterType $key ";
                }
                if (is_array($rawvalue)) {
                    $sql .= ' IN (' . str_repeat('?,', count($rawvalue) - 1) . '?) ';
                    $filterTab = array_merge($filterTab, $rawvalue);
                } else {
                    $sql .= ' LIKE ? ';
                    $value = trim($rawvalue);
                    $value = str_replace("\\", "\\\\", $value);
                    $value = str_replace("_", "\_", $value);
                    $value = str_replace(" ", "\ ", $value);
                    $filterTab[] = $value;
                }
            }
        }
        if (isset($order) && isset($sort) && (strtoupper($sort) == "ASC" || strtoupper($sort) == "DESC")) {
            $sql .= " ORDER BY $order $sort ";
        }
        if (isset($count) && $count != -1) {
            $sql = $this->db->limit($sql, $count, $offset);
        }
        return $this->getResult($sql, $filterTab, "fetchAll");
    }

    /**
     * Used for updating contact in database
     *
     * @param int $contactId
     * @param array $params
     * @return void
     */
    public function update($contactId, $params = array())
    {
        $sql = "UPDATE $this->table SET ";
        $sqlUpdate = "";
        $sqlParams = array();
        $not_null_attributes = array();

        // Store password value and remove it from the array to not inserting it in contact table.
        if (isset($params['contact_passwd'])) {
            $password = $params['contact_passwd'];
            unset($params['contact_passwd']);
        }

        if (array_search("", $params)) {
            $sql_attr = "SHOW FIELDS FROM $this->table";
            $res = $this->getResult($sql_attr, array(), "fetchAll");
            foreach ($res as $tab) {
                if ($tab['Null'] == 'NO') {
                    $not_null_attributes[$tab['Field']] = true;
                }
            }
        }

        foreach ($params as $key => $value) {
            if ($key == $this->primaryKey) {
                continue;
            }
            if ($sqlUpdate != "") {
                $sqlUpdate .= ",";
            }
            $sqlUpdate .= $key . " = ? ";
            if ($value === "" && !isset($not_null_attributes[$key])) {
                $value = null;
            }
            if (!is_null($value)) {
                $value = str_replace("<br/>", "\n", $value);
            }
            $sqlParams[] = $value;
        }

        if ($sqlUpdate) {
            $sqlParams[] = $contactId;
            $sql .= $sqlUpdate . " WHERE $this->primaryKey = ?";
            $this->db->query($sql, $sqlParams);
        }

        if (isset($password) && isset($contactId)) {
            //Get three last saved password.
            $statement = $this->db->prepare(
                'SELECT id, password, creation_date from `contact_password`
                WHERE `contact_id` = :contactId ORDER BY `creation_date` DESC'
            );
            $statement->bindValue(':contactId', $contactId, PDO::PARAM_INT);
            $statement->execute();
            //If 3 or more passwords are saved, delete the oldest one.
            if (($result = $statement->fetchAll()) && count($result) >= 3) {
                $creationDates = array_column($result, 'creation_date');
                $oldestPassword = $result[array_search(min($creationDates), $creationDates)];
                $statement = $this->db->prepare(
                    'DELETE FROM `contact_password` WHERE `id` < :id AND contact_id = :contactId'
                );
                $statement->bindValue(':id', $oldestPassword['id'], PDO::PARAM_INT);
                $statement->bindValue(':contactId', $contactId, PDO::PARAM_INT);
                $statement->execute();
            }

            $statement = $this->db->prepare(
                'INSERT INTO `contact_password` (`password`, `contact_id`, `creation_date`)
                VALUES (:password, :contactId, :creationDate)'
            );
            $statement->bindValue(':password', $password, PDO::PARAM_STR);
            $statement->bindValue(':contactId', $contactId, PDO::PARAM_INT);
            $statement->bindValue(':creationDate', time(), PDO::PARAM_STR);
            $statement->execute();
        }
    }
}
