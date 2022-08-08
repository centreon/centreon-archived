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


function DsHsrTestExistence($name = null)
{
    global $pearDB, $form;
    $formValues = array();
    if (isset($form)) {
        $formValues = $form->getSubmitValues();
    }

    $query = 'SELECT compo_id FROM giv_components_template WHERE ds_name = :ds_name';

    if (!empty($formValues['host_id'])) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $formValues['host_id'], $matches)) {
            $formValues['host_id'] = (int) $matches[1];
            $formValues['service_id'] = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('host_id must be a combination of integers');
        }
    }

    if (!empty($formValues['host_id']) && !empty($formValues['service_id'])) {
        $query .= ' AND host_id = :hostId AND service_id = :serviceId';
        $hostId = (filter_var($formValues['host_id'], FILTER_VALIDATE_INT) === false)
            ? null
            : (int) $formValues['host_id'];
        $serviceId = (filter_var($formValues['service_id'], FILTER_VALIDATE_INT) === false)
            ? null
            : (int) $formValues['service_id'];
    } else {
        $query .= ' AND host_id IS NULL AND service_id IS NULL';
    }

    $stmt = $pearDB->prepare($query);

    $stmt->bindValue(':ds_name', $name, \PDO::PARAM_STR);

    if (!empty($hostId) && !empty($serviceId)) {
        $stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
    }

    $stmt->execute();
    $compo = $stmt->fetch();
    if ($stmt->rowCount() >= 1 && $compo['compo_id'] === $formValues['compo_id']) {
        return true;
    } elseif ($stmt->rowCount() >= 1 && $compo['compo_id'] !== $formValues['compo_id']) {
        return false;
    } else {
        return true;
    }
}

function NameHsrTestExistence($name = null)
{
    global $pearDB, $form;
    $formValues = [];
    $bindParams = [];

    if (isset($form)) {
        $formValues = $form->getSubmitValues();
    }
    $query = 'SELECT compo_id FROM giv_components_template WHERE name = :name';
    if (!empty($formValues['host_id'])) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $formValues['host_id'], $matches)) {
            $formValues['host_id'] = (int) $matches[1];
            $formValues['service_id'] = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('chartId must be a combination of integers');
        }
    }

    if (!empty($formValues['host_id']) && !empty($formValues['service_id'])) {
        $query .= ' AND host_id = :hostId AND service_id = :serviceId';
        $hostId = (filter_var($formValues['host_id'], FILTER_VALIDATE_INT) === false)
            ? null
            : (int) $formValues['host_id'];
        $serviceId = (filter_var($formValues['service_id'], FILTER_VALIDATE_INT) === false)
            ? null
            : (int) $formValues['service_id'];
    } else {
        $query .= ' AND host_id IS NULL  AND service_id IS NULL';
    }

    $stmt = $pearDB->prepare($query);

    $stmt->bindValue(':name', $name, \PDO::PARAM_STR);

    if (!empty($hostId) && !empty($serviceId)) {
        $stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
    }

    $stmt->execute();
    $compo = $stmt->fetch();
    if ($stmt->rowCount() >= 1 && $compo['compo_id'] === $formValues['compo_id']) {
        return true;
    } elseif ($stmt->rowCount() >= 1 && $compo['compo_id'] !== $formValues['compo_id']) {
        return false;
    } else {
        return true;
    }
}

function checkColorFormat($color)
{
    if ($color != "" && strncmp($color, '#', 1)) {
        return false;
    } else {
        return true;
    }
}

/**
 * DELETE components in the database
 *
 * @param array $compos
 * @return void
 */
function deleteComponentTemplateInDB($compos = [])
{
    global $pearDB;
    $query = 'DELETE FROM giv_components_template WHERE compo_id IN (';

    foreach (array_keys($compos) as $compoId) {
        $query .= ':key_' . $compoId . ', ';
    }
    $query = rtrim($query, ', ');
    $query .= ')';

    $stmt = $pearDB->prepare($query);

    foreach (array_keys($compos) as $compoId) {
        $stmt->bindValue(':key_' . $compoId, $compoId, \PDO::PARAM_INT);
    }

    $stmt->execute();
    defaultOreonGraph();
}

function defaultOreonGraph()
{
    global $pearDB;
    $dbResult = $pearDB->query("SELECT DISTINCT compo_id FROM giv_components_template WHERE default_tpl1 = '1'");
    if (!$dbResult->rowCount()) {
        $dbResult2 = $pearDB->query("UPDATE giv_components_template SET default_tpl1 = '1' LIMIT 1");
    }
}

function noDefaultOreonGraph()
{
    global $pearDB;
    $rq = "UPDATE giv_components_template SET default_tpl1 = '0'";
    $dbResult = $pearDB->query($rq);
}

function multipleComponentTemplateInDB($compos = [], $nbrDup = [])
{
    global $pearDB;
    foreach ($compos as $key => $value) {
        $stmt = $pearDB->prepare(
            'SELECT * FROM giv_components_template WHERE compo_id = :compo_id LIMIT 1'
        );
        $stmt->bindValue(':compo_id', $key, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $row['compo_id'] = '';
        $row['default_tpl1'] = '0';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "name" ? ($name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
            }
            if (NameHsrTestExistence($name)) {
                $val ? $rq = "INSERT INTO giv_components_template VALUES (" . $val . ")" : $rq = null;
                $dbResult2 = $pearDB->query($rq);
            }
        }
    }
}

function updateComponentTemplateInDB($compoId = null)
{
    if (!$compoId) {
        return;
    }
    updateComponentTemplate($compoId);
}

function insertComponentTemplateInDB()
{
    $compoId = insertComponentTemplate();
    return ($compoId);
}

function insertComponentTemplate()
{
    global $form, $pearDB;
    $formValues = [];
    $formValues = $form->getSubmitValues();

    if (
        (isset($formValues['ds_filled']) && $formValues['ds_filled'] === '1') &&
        (!isset($formValues['ds_color_area']) || empty($formValues['ds_color_area']))
    ) {
        $formValues['ds_color_area'] = $formValues['ds_color_line'];
    }

    list($formValues['host_id'], $formValues['service_id']) = parseHostIdPostParameter($formValues['host_id']);

    $bindParams = sanitizeFormComponentTemplatesParameters($formValues);

    $params = [];
    foreach (array_keys($bindParams) as $token) {
        $params[] = ltrim($token, ':');
    }

    $query = 'INSERT INTO `giv_components_template` (`compo_id`, ';
    $query .= implode(', ', $params) . ') ';

    $query .= 'VALUES (NULL, ' . implode(', ', array_keys($bindParams)) . ')';
    $stmt = $pearDB->prepare($query);
    foreach ($bindParams as $token => list($paramType, $value)) {
        $stmt->bindValue($token, $value, $paramType);
    }
    $stmt->execute();
    defaultOreonGraph();
    $result = $pearDB->query('SELECT MAX(compo_id) FROM giv_components_template');
    $compoId = $result->fetch();
    return ($compoId["MAX(compo_id)"]);
}

/**
 * Parses the host_id parameter from the form and checks the hostId-serviceId format
 * and returns the hostId et serviceId when defined.
 *
 * @param string|null $hostIdParameter
 * @return array
 */
function parseHostIdPostParameter(?string $hostIdParameter): array
{
    if (!empty($hostIdParameter)) {
        if (preg_match('/([0-9]+)-([0-9]+)/', $hostIdParameter, $matches)) {
            $hostId = (int) $matches[1];
            $serviceId = (int) $matches[2];
        } else {
            throw new \InvalidArgumentException('host_id must be a combination of integers');
        }
    } else {
        $hostId = null;
        $serviceId = null;
    }

    return [$hostId, $serviceId];
}

function updateComponentTemplate($compoId = null)
{
    if (!$compoId) {
        return;
    }
    global $form, $pearDB;
    $formValues = [];
    $formValues = $form->getSubmitValues();

    if (
        (array_key_exists('ds_filled', $formValues) && $formValues['ds_filled'] === '1') &&
        (!array_key_exists('ds_color_area', $formValues) || empty($formValues['ds_color_area']))
    ) {
        $formValues['ds_color_area'] = $formValues['ds_color_line'];
    }

    list($formValues['host_id'], $formValues['service_id']) = parseHostIdPostParameter($formValues['host_service_id']);

    // Sets the default values if they have not been sent (used to deselect the checkboxes)
    $checkBoxValueToSet = [
        'ds_stack',
        'ds_invert',
        'ds_filled',
        'ds_hidecurve',
        'ds_max',
        'ds_min',
        'ds_minmax_int',
        'ds_average',
        'ds_last',
        'ds_total'
    ];
    foreach ($checkBoxValueToSet as $element) {
        $formValues[$element] = $formValues[$element] ?? '0';
    }

    $bindParams = sanitizeFormComponentTemplatesParameters($formValues);

    $query = 'UPDATE `giv_components_template` SET ';

    foreach (array_keys($bindParams) as $token) {
        $query .= ltrim($token, ':') . ' = ' . $token . ', ';
    }

    $query = rtrim($query, ', ');
    $query .= ' WHERE compo_id = :compo_id';

    $stmt = $pearDB->prepare($query);
    foreach ($bindParams as $token => list($paramType, $value)) {
            $stmt->bindValue($token, $value, $paramType);
    }
    $stmt->bindValue(':compo_id', $compoId, \PDO::PARAM_INT);
    $stmt->execute();

    defaultOreonGraph();
}

/**
 * Sanitize all the component templates parameters from the component template form
 * and return a ready to bind array.
 *
 * @param array $ret
 * @return array $bindParams
 */
function sanitizeFormComponentTemplatesParameters(array $ret): array
{
    $bindParams = [];
    foreach ($ret as $inputName => $inputValue) {
        switch ($inputName) {
            case 'name':
            case 'ds_name':
            case 'ds_color_line':
            case 'ds_color_area':
            case 'ds_color_area_warn':
            case 'ds_color_area_crit':
            case 'ds_legend':
            case 'comment':
            case 'ds_transparency':
                if (!empty($inputValue)) {
                    $inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING);
                    if (empty($inputValue)) {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR, null];
                    } else {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR, $inputValue];
                    }
                }
                break;
            case 'ds_color_line_mode':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR, in_array($inputValue[$inputName], ['0', '1'])
                        ? $inputValue[$inputName]
                        : '0'
                ];
                break;
            case 'ds_max':
            case 'ds_min':
            case 'ds_minmax_int':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR, in_array($inputValue, ['0', '1'])
                        ? $inputValue
                        : null
                ];
                break;
            case 'ds_average':
            case 'ds_last':
            case 'ds_total':
            case 'ds_stack':
            case 'ds_invert':
            case 'ds_filled':
            case 'ds_hidecurve':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR, in_array($inputValue, ['0', '1'])
                        ? $inputValue
                        : '0'
                ];
                break;
            case 'ds_jumpline':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR, in_array($inputValue, ['0', '1', '2', '3'])
                        ? $inputValue
                        : '0'
                ];
                break;
            case 'host_id':
            case 'service_id':
            case 'ds_tickness':
            case 'ds_order':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT, (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? null
                        : (int) $inputValue
                ];
                break;
            case 'default_tpl1':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT, (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? null
                        : (int) $inputValue
                ];
                defaultOreonGraph();
                break;
        }
    }
    return $bindParams;
}
