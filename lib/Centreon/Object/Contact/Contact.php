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
     * @inheritDoc
     */
    public function update($contactId, $params = [])
    {
        $sql = "UPDATE $this->table SET ";
        $sqlUpdate = "";
        $sqlParams = [];
        $notNullAttributes = [];

        if (isset($params['contact_autologin_key'])) {
            $statement = $this->db->prepare("SELECT contact_passwd FROM contact WHERE contact_id = :contactId ");
            $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
            $statement->execute();
            if (
                ($result = $statement->fetch(\PDO::FETCH_ASSOC))
                && (md5($params['contact_autologin_key']) === $result['contact_passwd']
                    || 'md5__' . md5($params['contact_autologin_key']) === $result['contact_passwd'])
            ) {
                throw new \Exception(_('Your autologin key must be different than your current password'));
            }
        }

        if (array_search("", $params)) {
            $sql_attr = "SHOW FIELDS FROM $this->table";
            $res = $this->getResult($sql_attr, [], "fetchAll");
            foreach ($res as $tab) {
                if ($tab['Null'] === 'NO') {
                    $notNullAttributes[$tab['Field']] = true;
                }
            }
        }
        foreach ($params as $key => $value) {
            if ($key === $this->primaryKey) {
                continue;
            }
            if ($sqlUpdate !== "") {
                $sqlUpdate .= ",";
            }
            $sqlUpdate .= $key . " = ? ";
            if ($value === "" && !isset($notNullAttributes[$key])) {
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
    }
}
