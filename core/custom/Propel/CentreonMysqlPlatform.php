<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace Centreon\Custom\Propel;

class CentreonMysqlPlatform extends \MysqlPlatform
{
    /**
     * Builds the DDL SQL to alter a table
     * based on a PropelTableDiff instance
     *
     * @return string
     */
    public function getModifyTableDDL(\PropelTableDiff $tableDiff)
    {
        $ret = '';

        // drop indices, foreign keys
        foreach ($tableDiff->getRemovedFks() as $fk) {
            $ret .= $this->getDropForeignKeyDDL($fk);
        }
        foreach ($tableDiff->getModifiedFks() as $fkName => $fkModification) {
            list($fromFk, $toFk) = $fkModification;
            $ret .= $this->getDropForeignKeyDDL($fromFk);
        }
        foreach ($tableDiff->getRemovedIndices() as $index) {
            $ret .= $this->getDropIndexDDL($index);
        }
        foreach ($tableDiff->getModifiedIndices() as $indexName => $indexModification) {
            list($fromIndex, $toIndex) = $indexModification;
            $ret .= $this->getDropIndexDDL($fromIndex);
        }

        // alter table structure
        foreach ($tableDiff->getRenamedColumns() as $columnRenaming) {
            $ret .= $this->getRenameColumnDDL($columnRenaming[0], $columnRenaming[1]);
        }
        if ($modifiedColumns = $tableDiff->getModifiedColumns()) {
            $ret .= $this->getModifyColumnsDDL($modifiedColumns);
        }
        if ($addedColumns = $tableDiff->getAddedColumns()) {
            $ret .= $this->getAddColumnsDDL($addedColumns);
        }
        foreach ($tableDiff->getRemovedColumns() as $column) {
            $ret .= $this->getRemoveColumnDDL($column);
        }

        // add new indices and foreign keys
        if ($tableDiff->hasModifiedPk()) {
            if ($tableDiff->getFromTable()->getName() === $tableDiff->getToTable()->getName()) {
                $dropDDL = str_replace(";\n", ", ", $this->getDropPrimaryKeyDDL($tableDiff->getFromTable()));
                $addDDL = $this->getAddPrimaryKeyDDL($tableDiff->getToTable());
                $ret .= $dropDDL . (substr($addDDL, strpos($addDDL, "ADD PRIMARY")));
            } else {
                $ret .= $this->getDropPrimaryKeyDDL($tableDiff->getFromTable());
                $ret .= $this->getAddPrimaryKeyDDL($tableDiff->getToTable());
            }
        }
        foreach ($tableDiff->getModifiedIndices() as $indexName => $indexModification) {
            list($fromIndex, $toIndex) = $indexModification;
            $ret .= $this->getAddIndexDDL($toIndex);
        }
        foreach ($tableDiff->getAddedIndices() as $index) {
            $ret .= $this->getAddIndexDDL($index);
        }
        foreach ($tableDiff->getModifiedFks() as $fkName => $fkModification) {
            list($fromFk, $toFk) = $fkModification;
            $ret .= $this->getAddForeignKeyDDL($toFk);
        }
        foreach ($tableDiff->getAddedFks() as $fk) {
            $ret .= $this->getAddForeignKeyDDL($fk);
        }

        return $ret;
    }
    
        
    /**
     * 
     * @param \Table $table
     * @return type
     */
    protected function getTableOptions(\Table $table)
    {
        $dbVI = $table->getDatabase()->getVendorInfoForType('mysql');
        $tableVI = $table->getVendorInfoForType('mysql');
        $vi = $dbVI->getMergedVendorInfo($tableVI);
        $tableOptions = array();
        // List of supported table options
        // see http://dev.mysql.com/doc/refman/5.5/en/create-table.html
        $supportedOptions = array(
            'AutoIncrement'   => 'AUTO_INCREMENT',
            'AvgRowLength'    => 'AVG_ROW_LENGTH',
            'Charset'         => 'CHARACTER SET',
            'Checksum'        => 'CHECKSUM',
            'Collate'         => 'COLLATE',
            'Connection'      => 'CONNECTION',
            'DataDirectory'   => 'DATA DIRECTORY',
            'Delay_key_write' => 'DELAY_KEY_WRITE',
            'DelayKeyWrite'   => 'DELAY_KEY_WRITE',
            'IndexDirectory'  => 'INDEX DIRECTORY',
            'InsertMethod'    => 'INSERT_METHOD',
            'KeyBlockSize'    => 'KEY_BLOCK_SIZE',
            'MaxRows'         => 'MAX_ROWS',
            'MinRows'         => 'MIN_ROWS',
            'Pack_Keys'       => 'PACK_KEYS',
            'PackKeys'        => 'PACK_KEYS',
            'RowFormat'       => 'ROW_FORMAT',
            'Union'           => 'UNION',
            'Partition'       => 'PARTITION'
        );
        
        foreach ($supportedOptions as $name => $sqlName) {
            $parameterValue = null;

            if ($vi->hasParameter($name)) {
                $parameterValue = $vi->getParameter($name);
            } elseif ($vi->hasParameter($sqlName)) {
                $parameterValue = $vi->getParameter($sqlName);
            }

            if (!is_null($parameterValue)) {
                if ($name === 'Partition') {
                    $tableOptions[] = sprintf('%s %s', $sqlName, $this->parsePartitionCommand($parameterValue));
                } else {
                    $parameterValue = is_numeric($parameterValue) ? $parameterValue : $this->quote($parameterValue);
                    $tableOptions[] = sprintf('%s=%s', $sqlName, $parameterValue);
                }
            }
        }

        return $tableOptions;
    }
    
    /**
     * 
     * @param string $command
     */
    protected function parsePartitionCommand($command)
    {
        return $command;
    }
}
