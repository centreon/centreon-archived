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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

global $centreon_path;
require_once $centreon_path . "/www/class/centreonBroker.class.php";
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

class CentreonConfigurationObjects extends CentreonWebService
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @global type $centreon_path
     * @throws RestBadRequestException
     */
    public function getDefaultValues()
    {
        global $centreon_path;
        
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
        $targetedFile = $centreon_path . "/www/class/centreon$target.class.php";
        if (file_exists($targetedFile)) {
            require_once $targetedFile;
            $calledClass = 'Centreon' . $target;
            $defaultValuesParameters = $calledClass::getDefaultValuesParameters($field);
        }
        
        /**
         * 
         */
        if (count($defaultValuesParameters) == 0) {
            throw new RestBadRequestException("Bad parameters count");
        }
        
        /**
         * 
         */
        if ($defaultValuesParameters['type'] === 'simple') {
            $selectedValues = $this->retrieveSimpleValues($defaultValuesParameters['currentObject'], $id, $field);
        } elseif ($defaultValuesParameters['type'] === 'relation') {
            $selectedValues = $this->retrieveRelatedValues($defaultValuesParameters['relationObject'], $id);
        } else {
            throw new RestBadRequestException("Bad parameters");
        }
        
        /**
         * 
         */
        $finalDatas = array();
        if (count($selectedValues) > 0) {
            $finalDatas = $this->retrieveExternalObjectDatas($defaultValuesParameters['externalObject'], $selectedValues);
        }
        
        return $finalDatas;
    }
    
    /**
     * 
     * @param type $externalObject
     * @param type $values
     */
    private function retrieveExternalObjectDatas($externalObject, $values)
    {
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }
        $query = "SELECT $externalObject[id], $externalObject[name] "
            . "FROM $externalObject[table] "
            . "WHERE $externalObject[comparator] "
            . "IN ($explodedValues)";
        
        $tmpValues = array();
        $resRetrieval = $this->pearDB->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $tmpValues[] = array(
                'id' => $row[$externalObject['id']],
                'text' => $row[$externalObject['name']]
            );
        }
        
        return $tmpValues;
    }
    
    /**
     * 
     * @param integer $id
     * @param string $field
     * @return array
     */
    private function retrieveSimpleValues($currentObject, $id, $field)
    {
        $tmpValues = array();
        
        // Getting Current Values
        $queryValuesRetrieval = "SELECT `$field` FROM $currentObject[table] WHERE $currentObject[id] = $id";
        
        $resRetrieval = $this->pearDB->query($queryValuesRetrieval);
        while ($row = $resRetrieval->fetchRow()) {
            $tmpValues[] = $row[$field];
        }
        
        return $tmpValues;
    }
    
    /**
     * 
     * @param array $relationObject
     * @param integer $id
     * @return array
     */
    private function retrieveRelatedValues($relationObject, $id)
    {
        $tmpValues = array();
        
        $queryValuesRetrieval = "SELECT $relationObject[field] FROM $relationObject[table] WHERE $relationObject[comparator] = $id";
        $resRetrieval = $this->pearDB->query($queryValuesRetrieval);
        while ($row = $resRetrieval->fetchRow()) {
            $tmpValues[] = $row[$relationObject['field']];
        }
        
        return $tmpValues;
    }
}
