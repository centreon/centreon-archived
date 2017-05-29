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

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

class CentreonConfigurationObjects extends CentreonWebService
{
    /**
     * CentreonConfigurationObjects constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getDefaultValues()
    {
        // Get Object targeted
        if (isset($this->arguments['id']) && !empty($this->arguments['id'])) {
            $id = $this->arguments['id'];
        } else {
            throw new RestBadRequestException("Bad parameters id");
        }

        // Get Object targeted
        if (isset($this->arguments['field'])) {
            $field = $this->arguments['field'];
        } else {
            throw new RestBadRequestException("Bad parameters field");
        }

        // Get Object targeted
        if (isset($this->arguments['target'])) {
            $target = ucfirst($this->arguments['target']);
        } else {
            throw new RestBadRequestException("Bad parameters target");
        }

        $defaultValuesParameters = array();
        $targetedFile = _CENTREON_PATH_ . "/www/class/centreon$target.class.php";
        if (file_exists($targetedFile)) {
            require_once $targetedFile;
            $calledClass = 'Centreon' . $target;
            $defaultValuesParameters = $calledClass::getDefaultValuesParameters($field);
        }

        if (count($defaultValuesParameters) == 0) {
            throw new RestBadRequestException("Bad parameters count");
        }

        if (isset($defaultValuesParameters['type']) && $defaultValuesParameters['type'] === 'simple') {
            if (isset($defaultValuesParameters['reverse']) && $defaultValuesParameters['reverse']) {
                $selectedValues = $this->retrieveSimpleValues(
                    array(
                        'table' => $defaultValuesParameters['externalObject']['table'],
                        'id' => $defaultValuesParameters['currentObject']['id']
                    ),
                    $id,
                    $defaultValuesParameters['externalObject']['id']
                );
            } else {
                $selectedValues = $this->retrieveSimpleValues($defaultValuesParameters['currentObject'], $id, $field);
            }
        } elseif (isset($defaultValuesParameters['type']) && $defaultValuesParameters['type'] === 'relation') {
            $selectedValues = $this->retrieveRelatedValues($defaultValuesParameters['relationObject'], $id);
        } else {
            throw new RestBadRequestException("Bad parameters");
        }

        # Manage final data
        $finalDatas = array();
        if (count($selectedValues) > 0) {
            $finalDatas = $this->retrieveExternalObjectDatas(
                $defaultValuesParameters['externalObject'],
                $selectedValues
            );
        }

        return $finalDatas;
    }

    /**
     *
     * @param array $externalObject
     * @param array $values
     */
    protected function retrieveExternalObjectDatas($externalObject, $values)
    {
        $tmpValues = array();

        if (isset($externalObject['object'])) {
            $classFile = $externalObject['object'] . '.class.php';
            include_once _CENTREON_PATH_ . "/www/class/$classFile";
            $calledClass = ucfirst($externalObject['object']);
            $externalObjectInstance = new $calledClass($this->pearDB);

            $options = array();
            if (isset($externalObject['objectOptions'])) {
                $options = $externalObject['objectOptions'];
            }

            $tmpValues = $externalObjectInstance->getObjectForSelect2($values, $options);
        } else {
            $explodedValues = '';

            if (!empty($values)) {
                for ($i = 1; $i <= count($values); $i++) {
                    $explodedValues .= '?,';
                }
                $explodedValues = substr($explodedValues, 0, -1);
            }

            $query = "SELECT $externalObject[id], $externalObject[name] " .
                "FROM $externalObject[table] " .
                "WHERE $externalObject[comparator] " .
                "IN ($explodedValues)";
            $stmt = $this->pearDB->prepare($query);
            $resRetrieval = $this->pearDB->execute($stmt, $values);

            if (PEAR::isError($resRetrieval)) {
                die("An error occured");
            }
            while ($row = $resRetrieval->fetchRow()) {
                $tmpValues[] = array(
                    'id' => $row[$externalObject['id']],
                    'text' => $row[$externalObject['name']]
                );
            }
        }
        return $tmpValues;
    }

    /**
     * @param $currentObject
     * @param $id
     * @param $field
     * @return array
     */
    protected function retrieveSimpleValues($currentObject, $id, $field)
    {
        $tmpValues = array();

        $fields = array();
        $fields[] = $field;
        if (isset($currentObject['additionalField'])) {
            $fields[] = $currentObject['additionalField'];
        }

        // Getting Current Values
        $queryValuesRetrieval = "SELECT " . implode(', ', $fields) . " "
            . "FROM " . $currentObject['table'] . " "
            . "WHERE " . $currentObject['id'] . " = ?";

        $stmt = $this->pearDB->prepare($queryValuesRetrieval);
        $resRetrieval = $this->pearDB->execute($stmt, array((int)$id));
        if (PEAR::isError($resRetrieval)) {
            die("An error occured");
        }
        while ($row = $resRetrieval->fetchRow()) {
            $tmpValue = $row[$field];
            if (isset($currentObject['additionalField'])) {
                $tmpValue .= '-' . $row[$currentObject['additionalField']];
            }
            $tmpValues[] = $tmpValue;
        }

        return $tmpValues;
    }

    /**
     * @param $relationObject
     * @param $id
     * @return array
     */
    protected function retrieveRelatedValues($relationObject, $id)
    {
        $tmpValues = array();

        $fields = array();
        $fields[] = $relationObject['field'];
        if (isset($relationObject['additionalField'])) {
            $fields[] = $relationObject['additionalField'];
        }

        $queryValuesRetrieval = "SELECT " . implode(', ', $fields) . " " .
            "FROM " . $relationObject['table'] . " " .
            "WHERE " . $relationObject['comparator'] . " = ?";
        $stmt = $this->pearDB->prepare($queryValuesRetrieval);
        $resRetrieval = $this->pearDB->execute($stmt, array((int)$id));
        if (PEAR::isError($resRetrieval)) {
            die("An error occured");
        }
        while ($row = $resRetrieval->fetchRow()) {
            if (!empty($row[$relationObject['field']])) {
                $tmpValue = $row[$relationObject['field']];
                if (isset($relationObject['additionalField'])) {
                    $tmpValue .= '-' . $row[$relationObject['additionalField']];
                }
                $tmpValues[] = $tmpValue;
            }
        }
        return $tmpValues;
    }
}
