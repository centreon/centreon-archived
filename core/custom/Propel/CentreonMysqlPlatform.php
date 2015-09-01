<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
