<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    DbUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */

/**
 * Provides functionality to retrieve meta data from a Firebird database.
 *
 * @package    DbUnit
 * @author     Matheus Degiovani (matheus@gigatron.com.br)
 * @copyright  2002-2012 Matheus Degiovani (matheus@gigatron.com.br)
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 1.1.2
 * @link       http://www.phpunit.de/
 * @since
 */
class PHPUnit_Extensions_Database_DB_MetaData_Firebird extends PHPUnit_Extensions_Database_DB_MetaData
{
    /**
     * The command used to perform a TRUNCATE operation.
     * @var string
     */
    protected $truncateCommand = 'DELETE FROM';

    /**
     * Returns an array containing the names of all the tables in the database.
     *
     * @return array
     */
    public function getTableNames()
    {
        $query = "
            SELECT DISTINCT
                TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
                TABLE_TYPE='BASE TABLE' AND
                TABLE_SCHEMA = ?
            ORDER BY TABLE_NAME
        ";

        $query =  "
            select
              RDB$RELATION_NAME as TABLE_NAME
            from RDB$RELATIONS
            where
              ((RDB$RELATION_TYPE = 0) or
               (RDB$RELATION_TYPE is null)) and
              (RDB$SYSTEM_FLAG = 0)
            order by (RDB$RELATION_NAME)
        ";


        $statement = $this->pdo->prepare($query);
        $statement->execute(array($this->getSchema()));

        $tableNames = array();
        while ($tableName = $statement->fetchColumn(0)) {
            $tableNames[] = $tableName;
        }

        return $tableNames;
    }

    /**
     * Returns an array containing the names of all the columns in the
     * $tableName table,
     *
     * @param string $tableName
     * @return array
     */
    public function getTableColumns($tableName)
    {
        if (!isset($this->columns[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->columns[$tableName];
    }

    /**
     * Returns an array containing the names of all the primary key columns in
     * the $tableName table.
     *
     * @param string $tableName
     * @return array
     */
    public function getTablePrimaryKeys($tableName)
    {
        if (!isset($this->keys[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->keys[$tableName];
    }

    /**
     * Loads column info from a database table.
     *
     * @param string $tableName
     */
    protected function loadColumnInfo($tableName)
    {
        $this->columns[$tableName] = array();
        $this->keys[$tableName]    = array();

        $columnQuery = "
            SELECT DISTINCT
                COLUMN_NAME, ORDINAL_POSITION
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_NAME = ? AND
                TABLE_SCHEMA = ?
            ORDER BY ORDINAL_POSITION
        ";


        $columnQuery = "
            select
              rf.RDB\$FIELD_NAME as COLUMN_NAME,
              rf.RDB\$FIELD_POSITION as ORDINAL_POSITION
            from RDB\$RELATION_FIELDS as rf
            where
              upper(RDB\$RELATION_NAME) = upper(?)
            order by
              ORDINAL_POSITION

        ";


        $columnStatement = $this->pdo->prepare($columnQuery);
        $columnStatement->execute(array($tableName));

        while ($columName = $columnStatement->fetchColumn(0)) {
            $this->columns[$tableName][] = $columName;
        }

        $keyQuery = "
            SELECT
                KCU.COLUMN_NAME,
                KCU.ORDINAL_POSITION
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE as KCU
            LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS as TC
                ON TC.TABLE_NAME = KCU.TABLE_NAME
            WHERE
                TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
                TC.TABLE_NAME = ? AND
                TC.TABLE_SCHEMA = ?
            ORDER BY
                KCU.ORDINAL_POSITION ASC
        ";

        $keyQuery = "
            select
              idseg.rdb\$field_name as COLUMN_NAME,
              idseg.rdb\$field_position as ORDINAL_POSITION,
              rc.rdb\$relation_name as tablename,
              rc.rdb\$constraint_name as pk_name
            from
              RDB\$RELATION_CONSTRAINTS AS rc
                left join
              rdb\$index_segments as idseg on
                (rc.rdb\$index_name = idseg.rdb\$index_name)
            where
              rc.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY'
              and upper(rc.RDB\$RELATION_NAME) = upper(?)
            order by
              rc.rdb\$constraint_name, idseg.rdb\$field_position
        ";

        $keyStatement = $this->pdo->prepare($keyQuery);
        $keyStatement->execute(array($tableName));

        while ($columName = $keyStatement->fetchColumn(0)) {
            $this->keys[$tableName][] = $columName;
        }
    }

    /**
     * Returns the schema for the connection.
     *
     * @return string
     */
    public function getSchema()
    {
        if (empty($this->schema)) {
            return 'public';
        } else {
            return $this->schema;
        }
    }

    /**
     * Returns true if the rdbms allows cascading
     *
     * @return bool
     */
    public function allowsCascading()
    {
        return false;
    }

    /**
     * Returns a quoted schema object. (table name, column name, etc)
     *
     * @param string $object
     * @return string
     */
    public function quoteSchemaObject($object) {
        return $object; //firebird does not allow object quoting
    }
}
