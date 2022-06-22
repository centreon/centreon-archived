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

if (!isset($centreon)) {
    exit();
}

function includeExcludeTimeperiods($tpId, $includeTab = array(), $excludeTab = array())
{
    global $pearDB;

    /*
     * Insert inclusions
     */
    if (isset($includeTab) && is_array($includeTab)) {
        $str = "";
        foreach ($includeTab as $tpIncludeId) {
            if ($str != "") {
                $str .= ", ";
            }
            $str .= "('" . $tpId . "', '" . $tpIncludeId . "')";
        }
        if (strlen($str)) {
            $query = "INSERT INTO timeperiod_include_relations (timeperiod_id, timeperiod_include_id ) VALUES " . $str;
            $pearDB->query($query);
        }
    }

    /*
     * Insert exclusions
     */
    if (isset($excludeTab) && is_array($excludeTab)) {
        $str = "";
        foreach ($excludeTab as $tpExcludeId) {
            if ($str != "") {
                $str .= ", ";
            }
            $str .= "('" . $tpId . "', '" . $tpExcludeId . "')";
        }
        if (strlen($str)) {
            $query = "INSERT INTO timeperiod_exclude_relations (timeperiod_id, timeperiod_exclude_id ) VALUES " . $str;
            $pearDB->query($query);
        }
    }
}

function testTPExistence($name = null)
{
    global $pearDB, $form, $centreon;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('tp_id');
    }

    $query = 'SELECT tp_name, tp_id FROM timeperiod WHERE tp_name = :tp_name';
    $statement = $pearDB->prepare($query);
    $statement->bindValue(
        ':tp_name',
        htmlentities($centreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8"),
        \PDO::PARAM_STR
    );
    $statement->execute();
    $tp = $statement->fetch(\PDO::FETCH_ASSOC);
    #Modif case
    if ($statement->rowCount() >= 1 && $tp["tp_id"] == $id) {
        return true;
    } elseif ($statement->rowCount() >= 1 && $tp["tp_id"] != $id) { #Duplicate entry
        return false;
    } else {
        return true;
    }
}

function deleteTimeperiodInDB($timeperiods = array())
{
    global $pearDB, $centreon;
    foreach ($timeperiods as $key => $value) {
        $dbResult2 = $pearDB->query("SELECT tp_name FROM `timeperiod` WHERE `tp_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $dbResult = $pearDB->query("DELETE FROM timeperiod WHERE tp_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("timeperiod", $key, $row['tp_name'], "d");
    }
}

function multipleTimeperiodInDB($timeperiods = array(), $nbrDup = array())
{
    global $centreon;

    foreach ($timeperiods as $key => $value) {
        global $pearDB;

        $fields = array();
        $dbResult = $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '" . $key . "' LIMIT 1");

        $query = "SELECT days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = '" . $key . "'";
        $res = $pearDB->query($query);
        while ($row = $res->fetch()) {
            foreach ($row as $keyz => $valz) {
                $fields[$keyz] = $valz;
            }
        }

        $row = $dbResult->fetch();
        $row["tp_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = [];
            foreach ($row as $key2 => $value2) {
                if ($key2 == "tp_name") {
                    $value2 .= "_" . $i;
                }
                $key2 == "tp_name" ? ($tp_name = $value2) : "";
                $val[] = $value2 ?: null;
                if ($key2 != "tp_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($tp_name)) {
                    $fields["tp_name"] = $tp_name;
                }
            }
            if (isset($tp_name) && testTPExistence($tp_name)) {
                $params = [
                    'values' => $val,
                    'timeperiod_id' => $key
                ];
                $tpId = duplicateTimePeriod($params);
                $centreon->CentreonLogAction->insertLog("timeperiod", $tpId, $tp_name, "a", $fields);
            }
        }
    }
}

function updateTimeperiodInDB($tp_id = null)
{
    if (!$tp_id) {
        return;
    }
    updateTimeperiod($tp_id);
}

function updateTimeperiod($tp_id, $params = array())
{
    global $form, $pearDB, $centreon;

    if (!$tp_id) {
        return;
    }
    $ret = array();
    if (count($params)) {
        $ret = $params;
    } else {
        $ret = $form->getSubmitValues();
    }

    $ret["tp_name"] = $centreon->checkIllegalChar($ret["tp_name"]);

    $rq = "UPDATE timeperiod ";
    $rq .= "SET tp_name = '" . htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_alias = '" . htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_sunday = '" . htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_monday = '" . htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_tuesday = '" . htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_wednesday = '" . htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_thursday = '" . htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_friday = '" . htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8") . "', " .
        "tp_saturday = '" . htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8") . "' " .
        "WHERE tp_id = '" . $tp_id . "'";
    $pearDB->query($rq);

    $pearDB->query("DELETE FROM timeperiod_include_relations WHERE timeperiod_id = '" . $tp_id . "'");
    $pearDB->query("DELETE FROM timeperiod_exclude_relations WHERE timeperiod_id = '" . $tp_id . "'");

    if (!isset($ret['tp_include'])) {
        $ret['tp_include'] = array();
    }
    if (!isset($ret['tp_exclude'])) {
        $ret['tp_exclude'] = array();
    }

    includeExcludeTimeperiods($tp_id, $ret['tp_include'], $ret['tp_exclude']);

    if (isset($_POST['nbOfExceptions'])) {
        $my_tab = $_POST;
        $already_stored = array();
        $pearDB->query("DELETE FROM `timeperiod_exceptions` WHERE `timeperiod_id`='" . $tp_id . "'");
        for ($i = 0; $i <= $my_tab['nbOfExceptions']; $i++) {
            $exInput = "exceptionInput_" . $i;
            $exValue = "exceptionTimerange_" . $i;
            if (isset($my_tab[$exInput]) &&
                !isset($already_stored[strtolower($my_tab[$exInput])]) &&
                $my_tab[$exInput]
            ) {
                $query = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) " .
                    "VALUES ('" . $tp_id . "', LOWER('" . $pearDB->escape($my_tab[$exInput]) . "'), '" .
                    $pearDB->escape($my_tab[$exValue]) . "')";
                $pearDB->query($query);
                $fields[$my_tab[$exInput]] = $my_tab[$exValue];
                $already_stored[strtolower($my_tab[$exInput])] = 1;
            }
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "timeperiod",
        $tp_id,
        htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8"),
        "c",
        $fields
    );
}

function insertTimeperiodInDB($ret = array())
{
    $tp_id = insertTimeperiod($ret);
    return ($tp_id);
}

function insertTimeperiod($ret = array(), $exceptions = null)
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $ret["tp_name"] = $centreon->checkIllegalChar($ret["tp_name"]);

    $rq = "INSERT INTO timeperiod ";
    $rq .= "(tp_name, tp_alias, tp_sunday, tp_monday, tp_tuesday, tp_wednesday, tp_thursday, tp_friday, tp_saturday) ";
    $rq .= "VALUES (";
    isset($ret["tp_name"]) && $ret["tp_name"] != null
        ? $rq .= "'" . htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_alias"]) && $ret["tp_alias"] != null
        ? $rq .= "'" . htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_sunday"]) && $ret["tp_sunday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_monday"]) && $ret["tp_monday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_tuesday"]) && $ret["tp_tuesday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_wednesday"]) && $ret["tp_wednesday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_thursday"]) && $ret["tp_thursday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_friday"]) && $ret["tp_friday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["tp_saturday"]) && $ret["tp_saturday"] != null
        ? $rq .= "'" . htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8") . "'"
        : $rq .= "NULL";
    $rq .= ")";
    $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(tp_id) FROM timeperiod");
    $tp_id = $dbResult->fetch();

    if (!isset($ret['tp_include'])) {
        $ret['tp_include'] = array();
    }
    if (!isset($ret['tp_exclude'])) {
        $ret['tp_exclude'] = array();
    }

    includeExcludeTimeperiods($tp_id['MAX(tp_id)'], $ret['tp_include'], $ret['tp_exclude']);

    /*
     *  Insert exceptions
     */
    if (isset($exceptions)) {
        $my_tab = $exceptions;
    } elseif (isset($_POST['nbOfExceptions'])) {
        $my_tab = $_POST;
    }
    if (isset($my_tab['nbOfExceptions'])) {
        $already_stored = array();
        $query = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) " .
                 "VALUES (:timeperiod_id, :days, :timerange)";
        $statement = $pearDB->prepare($query);
        for ($i = 0; $i <= $my_tab['nbOfExceptions']; $i++) {
            $exInput = "exceptionInput_" . $i;
            $exValue = "exceptionTimerange_" . $i;
            if (
                isset($my_tab[$exInput]) && !isset($already_stored[strtolower($my_tab[$exInput])]) &&
                $my_tab[$exInput]
            ) {
                $statement->bindValue(':timeperiod_id', (int) $tp_id['MAX(tp_id)'], \PDO::PARAM_INT);
                $statement->bindValue(':days', strtolower($my_tab[$exInput]), \PDO::PARAM_STR);
                $statement->bindValue(':timerange', $my_tab[$exValue], \PDO::PARAM_STR);
                $statement->execute();
                $fields[$my_tab[$exInput]] = $my_tab[$exValue];
                $already_stored[strtolower($my_tab[$exInput])] = 1;
            }
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "timeperiod",
        $tp_id["MAX(tp_id)"],
        htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8"),
        "a",
        $fields
    );

    return ($tp_id["MAX(tp_id)"]);
}

function checkHours($hourString)
{
    if ($hourString == "") {
        return true;
    } else {
        if (strstr($hourString, ",")) {
            $tab1 = preg_split("/\,/", $hourString);
            for ($i = 0; isset($tab1[$i]); $i++) {
                if (preg_match("/([0-9]*):([0-9]*)-([0-9]*):([0-9]*)/", $tab1[$i], $str)) {
                    if ($str[1] > 24 || $str[3] > 24) {
                        return false;
                    }
                    if ($str[2] > 59 || $str[4] > 59) {
                        return false;
                    }
                    if (($str[3] * 60 * 60 + $str[4] * 60) > 86400 || ($str[1] * 60 * 60 + $str[2] * 60) > 86400) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return true;
        } else {
            if (preg_match("/([0-9]*):([0-9]*)-([0-9]*):([0-9]*)/", $hourString, $str)) {
                if ($str[1] > 24 || $str[3] > 24) {
                    return false;
                }
                if ($str[2] > 59 || $str[4] > 59) {
                    return false;
                }
                if (($str[3] * 60 * 60 + $str[4] * 60) > 86400 || ($str[1] * 60 * 60 + $str[2] * 60) > 86400) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        }
    }
}

/**
 * Get time period id by name
 *
 * @param string $name
 * @return int
 */
function getTimeperiodIdByName($name)
{
    global $pearDB;

    $id = 0;
    $res = $pearDB->query("SELECT tp_id FROM timeperiod WHERE tp_name = '" . $pearDB->escape($name) . "'");
    if ($res->rowCount()) {
        $row = $res->fetch();
        $id = $row['tp_id'];
    }
    return $id;
}

/**
 * Get chain of time periods via template relation
 *
 * @global \Pimple\Container $dependencyInjector
 * @param array $tpIds List of selected time period as IDs
 * @return array
 */
function getTimeperiodsFromTemplate(array $tpIds)
{
    global $dependencyInjector;

    $db = $dependencyInjector['centreon.db-manager'];

    $result = [];

    foreach ($tpIds as $tpId) {
        $db->getRepository(Centreon\Domain\Repository\TimePeriodRepository::class)
            ->getIncludeChainByParent($tpId, $result);
    }

    return $result;
}

/**
 * Validator prevent loops via template
 *
 * @global \HTML_QuickFormCustom $form Access to the form object
 * @param array $value List of selected time period as IDs
 * @return bool
 */
function testTemplateLoop($value)
{
    // skip check if template field is empty
    if (!$value) {
        return true;
    }

    global $form;

    $data = $form->getSubmitValues();

    // skip check if timeperiod is new
    if (!$data['tp_id']) {
        return true;
    } elseif (in_array($data['tp_id'], $value)) {
        // try to skip heavy check of templates

        return false;
    } elseif (in_array($data['tp_id'], getTimeperiodsFromTemplate($value))) {
        // get list of all timeperiods related via templates

        return false;
    }

    return true;
}

/**
 * All in one function to duplicate time periods
 *
 * @param array $params
 * @return int
 */
function duplicateTimePeriod(array $params): int
{
    global $pearDB;

    $isAlreadyInTransaction = $pearDB->inTransaction();
    if (!$isAlreadyInTransaction) {
        $pearDB->beginTransaction();
    }
    try {
        $params['tp_id'] = createTimePeriod($params);
        createTimePeriodsExceptions($params);
        createTimePeriodsIncludeRelations($params);
        createTimePeriodsExcludeRelations($params);
        if (!$isAlreadyInTransaction) {
            $pearDB->commit();
        }
    } catch (\Exception $e) {
        if (!$isAlreadyInTransaction) {
            $pearDB->rollBack();
        }
    }
    return $params['tp_id'];
}

/**
 * Creates time period and returns id.
 *
 * @param array $params
 * @return int
 */
function createTimePeriod(array $params): int
{
    global $pearDB;

    $queryBindValues = [];
    foreach ($params['values'] as $index => $value) {
        $queryBindValues[':value_' . $index] = $value;
    }
    $bindValues = implode(', ', array_keys($queryBindValues));
    $statement = $pearDB->prepare("INSERT INTO timeperiod VALUES ($bindValues)");
    foreach ($queryBindValues as $bindKey => $bindValue) {
        if (array_key_first($queryBindValues) === $bindKey) {
            $statement->bindValue($bindKey, (int) $bindValue, \PDO::PARAM_INT);
        } else {
            $statement->bindValue($bindKey, $bindValue, \PDO::PARAM_STR);
        }
    }
    $statement->execute();
    return (int) $pearDB->lastInsertId();
}

/**
 * Creates time periods exclude relations
 *
 * @param array $params
 */
function createTimePeriodsExcludeRelations(array $params): void
{
    global $pearDB;

    $query = "INSERT INTO timeperiod_exclude_relations (timeperiod_id, timeperiod_exclude_id) " .
             "SELECT :tp_id, timeperiod_exclude_id FROM timeperiod_exclude_relations " .
             "WHERE timeperiod_id = :timeperiod_id";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':tp_id', $params['tp_id'], \PDO::PARAM_INT);
    $statement->bindValue(':timeperiod_id', (int) $params['timeperiod_id'], \PDO::PARAM_INT);
    $statement->execute();
}

/**
 * Creates time periods include relations
 *
 * @param array $params
 */
function createTimePeriodsIncludeRelations(array $params): void
{
    global $pearDB;

    $query = "INSERT INTO timeperiod_include_relations (timeperiod_id, timeperiod_include_id) " .
             "SELECT :tp_id, timeperiod_include_id FROM timeperiod_include_relations " .
             "WHERE timeperiod_id = :timeperiod_id";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':tp_id', $params['tp_id'], \PDO::PARAM_INT);
    $statement->bindValue(':timeperiod_id', (int) $params['timeperiod_id'], \PDO::PARAM_INT);
    $statement->execute();
}

/**
 * Creates time periods exceptions
 *
 * @param array $params
 */
function createTimePeriodsExceptions(array $params): void
{
    global $pearDB;

    $query = "INSERT INTO timeperiod_exceptions (timeperiod_id, days, timerange) " .
             "SELECT :tp_id, days, timerange FROM timeperiod_exceptions " .
             "WHERE timeperiod_id = :timeperiod_id";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':tp_id', $params['tp_id'], \PDO::PARAM_INT);
    $statement->bindValue(':timeperiod_id', (int) $params['timeperiod_id'], \PDO::PARAM_INT);
    $statement->execute();
}
