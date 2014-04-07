<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Sebastian Marek <proofek@gmail.com>
 * @copyright  2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */

/**
 * @package    DbUnit
 * @author     Sebastian Marek <proofek@gmail.com>
 * @copyright  2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */
class Extensions_Database_DataSet_AbstractTableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_DataSet_QueryTable
     */
    protected $table;

    public function setUp()
    {
        $tableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
            'table', array('id', 'column1')
        );

        $this->table = new PHPUnit_Extensions_Database_DataSet_DefaultTable($tableMetaData);

        $this->table->addRow(array(
            'id' => 1,
            'column1' => 'randomValue'
        ));
    }

    /**
     * @param array $row
     * @param boolean $exists
     * @dataProvider providerTableContainsRow
     */
    public function testTableContainsRow($row, $exists)
    {
        $result = $this->table->assertContainsRow($row);
        $this->assertEquals($exists, $result);
    }

    public function providerTableContainsRow()
    {
        return array(
            array(array('id' => 1, 'column1' => 'randomValue'), true),
            array(array('id' => 1, 'column1' => 'notExistingValue'), false)
        );
    }

    public function testMatchesWithNonMatchingMetaData()
    {
        $tableMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');
        $otherMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');

        $otherTable = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITable');
        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->will($this->returnValue($otherMetaData));

        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->will($this->returnValue(false));

        $table = new PHPUnit_Extensions_Database_DataSet_DefaultTable($tableMetaData);
        $this->assertFalse($table->matches($otherTable));
    }

    public function testMatchesWithNonMatchingRowCount()
    {
        $tableMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');
        $otherMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');

        $otherTable = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITable');
        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->will($this->returnValue($otherMetaData));
        $otherTable->expects($this->once())
            ->method('getRowCount')
            ->will($this->returnValue(0));

        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->will($this->returnValue(true));

        $table = $this->getMock('PHPUnit_Extensions_Database_DataSet_DefaultTable', array('getRowCount'), array($tableMetaData));
        $table->expects($this->once())
            ->method('getRowCount')
            ->will($this->returnValue(1));
        $this->assertFalse($table->matches($otherTable));
    }

    /**
     * @param array $tableColumnValues
     * @param array $otherColumnValues
     * @param boolean $matches
     * @dataProvider providerMatchesWithColumnValueComparisons
     */
    public function testMatchesWithColumnValueComparisons($tableColumnValues, $otherColumnValues, $matches)
    {
        $tableMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');
        $otherMetaData = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITableMetaData');

        $otherTable = $this->getMock('PHPUnit_Extensions_Database_DataSet_ITable');
        $otherTable->expects($this->once())
            ->method('getTableMetaData')
            ->will($this->returnValue($otherMetaData));
        $otherTable->expects($this->once())
            ->method('getRowCount')
            ->will($this->returnValue(count($otherColumnValues)));

        $tableMetaData->expects($this->once())
            ->method('getColumns')
            ->will($this->returnValue(array_keys(reset($tableColumnValues))));
        $tableMetaData->expects($this->once())
            ->method('matches')
            ->with($otherMetaData)
            ->will($this->returnValue(true));

        $table = $this->getMock('PHPUnit_Extensions_Database_DataSet_DefaultTable', array('getRowCount', 'getValue'), array($tableMetaData));
        $table->expects($this->any())
            ->method('getRowCount')
            ->will($this->returnValue(count($tableColumnValues)));

        $tableMap = array();
        $otherMap = array();
        foreach ($tableColumnValues as $rowIndex => $rowData) {
            foreach ($rowData as $columnName => $columnValue) {
                $tableMap[] = array($rowIndex, $columnName, $columnValue);
                $otherMap[] = array($rowIndex, $columnName, $otherColumnValues[$rowIndex][$columnName]);
            }
        }
        $table->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($tableMap));
        $otherTable->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($otherMap));

        $this->assertSame($matches, $table->matches($otherTable));
    }

    public function providerMatchesWithColumnValueComparisons()
    {
        return array(

            // One row, one column, matches
            array(
                array(
                    array('id' => 1),
                ),
                array(
                    array('id' => 1),
                ),
                true,
            ),

            // One row, one column, does not match
            array(
                array(
                    array('id' => 1),
                ),
                array(
                    array('id' => 2),
                ),
                false,
            ),

            // Multiple rows, one column, matches
            array(
                array(
                    array('id' => 1),
                    array('id' => 2),
                ),
                array(
                    array('id' => 1),
                    array('id' => 2),
                ),
                true,
            ),

            // Multiple rows, one column, do not match
            array(
                array(
                    array('id' => 1),
                    array('id' => 2),
                ),
                array(
                    array('id' => 1),
                    array('id' => 3),
                ),
                false,
            ),

            // Multiple rows, multiple columns, matches
            array(
                array(
                    array('id' => 1, 'name' => 'foo'),
                    array('id' => 2, 'name' => 'bar'),
                ),
                array(
                    array('id' => 1, 'name' => 'foo'),
                    array('id' => 2, 'name' => 'bar'),
                ),
                true,
            ),

            // Multiple rows, multiple columns, do not match
            array(
                array(
                    array('id' => 1, 'name' => 'foo'),
                    array('id' => 2, 'name' => 'bar'),
                ),
                array(
                    array('id' => 1, 'name' => 'foo'),
                    array('id' => 2, 'name' => 'baz'),
                ),
                false,
            ),

            // Comparisons must include a type check
            array(
                array(
                    array('id' => 0),
                ),
                array(
                    array('id' => null),
                ),
                false,
            ),
        );
    }

}
