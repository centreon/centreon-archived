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


function testTrapGroupExistence($name = null)
{
    global $pearDB, $form;
    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('id');
    }
    $query = "SELECT traps_group_id as id FROM traps_group WHERE traps_group_name = '" .
        $pearDB->escape(htmlentities($name, ENT_QUOTES, "UTF-8")) . "'";
    $dbResult = $pearDB->query($query);
    $trap_group = $dbResult->fetch();
    # Modif case
    if ($dbResult->rowCount() >= 1 && $trap_group["id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($dbResult->rowCount() >= 1 && $trap_group["id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function deleteTrapGroupInDB($trap_groups = array())
{
    global $pearDB, $oreon;

    foreach ($trap_groups as $key => $value) {
        $query = "SELECT traps_group_name as name FROM `traps_group` WHERE `traps_group_id` = '" .
            $pearDB->escape($key) . "' LIMIT 1";
        $dbResult2 = $pearDB->query($query);
        $row = $dbResult2->fetch();

        $pearDB->query("DELETE FROM traps_group WHERE traps_group_id = '" . $pearDB->escape($key) . "'");
        $oreon->CentreonLogAction->insertLog("traps_group", $key, $row['name'], "d");
    }
}

function multipleTrapGroupInDB($trap_groups = array(), $nbrDup = array())
{
    global $pearDB, $oreon;

    foreach ($trap_groups as $key => $value) {
        $query = "SELECT * FROM traps_group WHERE traps_group_id = '" . $pearDB->escape($key) . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $row["traps_group_id"] = null;

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $name = "";
                $key2 == "traps_group_name" ? ($name = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "traps_group_id") {
                    $fields[$key2] = $value2;
                }
                $fields["name"] = $name;
            }

            if (testTrapGroupExistence($name)) {
                $val ? $rq = "INSERT INTO traps_group VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $oreon->CentreonLogAction->insertLog("traps_group", $key, $name, "a", $fields);

                $query = "INSERT INTO traps_group_relation (traps_group_id, traps_id) SELECT " .
                    "(SELECT MAX(traps_group_id) as max_id FROM traps_group), traps_id FROM traps_group_relation " .
                    "WHERE traps_group_id = '" . $pearDB->escape($key) . "'";
                $pearDB->query($query);
            }
        }
    }
}

function updateTrapGroupInDB($id = null)
{
    if (!$id) {
        return;
    }
    updateTrapGroup($id);
}

function updateTrapGroup($id = null)
{
    global $form, $pearDB, $oreon;

    if (!$id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();

    $rq = "UPDATE traps_group ";
    $rq .= "SET traps_group_name = '" . $pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8")) . "' ";
    $rq .= "WHERE traps_group_id = '" . $pearDB->escape($id) . "'";
    $pearDB->query($rq);

    $pearDB->query("DELETE FROM traps_group_relation WHERE traps_group_id = '" . $pearDB->escape($id) . "'");
    if (isset($ret['traps'])) {
        foreach ($ret['traps'] as $trap_id) {
            $query = "INSERT INTO traps_group_relation (traps_group_id, traps_id) VALUES (" . $pearDB->escape($id) .
                ",'" . $pearDB->escape($trap_id) . "')";
            $pearDB->query($query);
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("traps_group", $id, $fields["name"], "c", $fields);
}

function insertTrapGroupInDB($ret = array())
{
    $id = insertTrapGroup($ret);
    return ($id);
}

function insertTrapGroup($ret = array())
{
    global $form, $pearDB, $oreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $rq = "INSERT INTO traps_group ";
    $rq .= "(traps_group_name) ";
    $rq .= "VALUES ";
    $rq .= "('" . $pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8")) . "')";
    $dbResult = $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(traps_group_id) as max_id FROM traps_group");
    $trap_group_id = $dbResult->fetch();

    $fields = array();
    if (isset($ret['traps'])) {
        foreach ($ret['traps'] as $trap_id) {
            $query = "INSERT INTO traps_group_relation (traps_group_id, traps_id) VALUES (" .
                $pearDB->escape($trap_group_id['max_id']) . ",'" . $pearDB->escape($trap_id) . "')";
            $pearDB->query($query);
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("traps_group", $trap_group_id['max_id'], $fields['name'], 'a', $fields);

    return ($trap_group_id["max_id"]);
}
