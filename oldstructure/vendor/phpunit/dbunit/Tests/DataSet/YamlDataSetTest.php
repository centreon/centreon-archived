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
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */

/**
 * @package    DbUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */
class Extensions_Database_DataSet_YamlDataSetTest extends PHPUnit_Framework_TestCase
{
    protected $expectedDataSet;

    public function testYamlDataSet()
    {
        $table1MetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
            'table1', array('table1_id', 'column1', 'column2', 'column3', 'column4', 'extraColumn')
        );
        $table2MetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
            'table2', array('table2_id', 'column5', 'column6', 'column7', 'column8')
        );
        $emptyTableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
            'emptyTable', array()
        );

        $table1 = new PHPUnit_Extensions_Database_DataSet_DefaultTable($table1MetaData);
        $table2 = new PHPUnit_Extensions_Database_DataSet_DefaultTable($table2MetaData);
        $emptyTable = new PHPUnit_Extensions_Database_DataSet_DefaultTable($emptyTableMetaData);

        $table1->addRow(array(
            'table1_id' => 1,
            'column1' => 'tgfahgasdf',
            'column2' => 200,
            'column3' => 34.64,
            'column4' => 'yghkf;a  hahfg8ja h;'
        ));
        $table1->addRow(array(
            'table1_id' => 2,
            'column1' => 'hk;afg',
            'column2' => 654,
            'column3' => 46.54,
            'column4' => '24rwehhads',
            'extraColumn' => 'causes no worries'
        ));
        $table1->addRow(array(
            'table1_id' => 3,
            'column1' => 'ha;gyt',
            'column2' => 462,
            'column3' => 1654.4,
            'column4' => 'asfgklg'
        ));

        $table2->addRow(array(
            'table2_id' => 1,
            'column5' => 'fhah',
            'column6' => 456,
            'column7' => 46.5,
            'column8' => 'fsdb, ghfdas'
        ));
        $table2->addRow(array(
            'table2_id' => 2,
            'column5' => 'asdhfoih',
            'column6' => 654,
            'column7' => 'blah',
            'column8' => '43asd "fhgj" sfadh'
        ));
        $table2->addRow(array(
            'table2_id' => 3,
            'column5' => 'ajsdlkfguitah',
            'column6' => 654,
            'column7' => 'blah',
            'column8' => 'thesethasdl
asdflkjsadf asdfsadfhl "adsf, halsdf" sadfhlasdf'
        ));

        $expectedDataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table1, $table2, $emptyTable));

        $yamlDataSet = new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__FILE__) . '/../_files/YamlDataSets/testDataSet.yaml');

        PHPUnit_Extensions_Database_DataSet_YamlDataSet::write($yamlDataSet, sys_get_temp_dir() . '/yaml.dataset');

        PHPUnit_Extensions_Database_TestCase::assertDataSetsEqual($expectedDataSet, $yamlDataSet);
    }

    public function testAlternateParser() {
        $table1MetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
            'math_table', array('answer')
        );
        $table1 = new PHPUnit_Extensions_Database_DataSet_DefaultTable($table1MetaData);
        $table1->addRow(array(
            'answer' => 'pi/2'
        ));
        $expectedDataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table1));

        $parser = new Extensions_Database_DataSet_YamlDataSetTest_PiOver2Parser();
        $yamlDataSet = new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            dirname(__FILE__) . '/../_files/YamlDataSets/testDataSet.yaml',
            $parser);
        PHPUnit_Extensions_Database_TestCase::assertDataSetsEqual($expectedDataSet, $yamlDataSet);
    }
}

/**
 * A trivial YAML parser that always returns the same array.
 *
 * @package    DbUnit
 * @author     Yash Parghi <yash@yashparghi.com>
 * @copyright  2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.3.1
 */
class Extensions_Database_DataSet_YamlDataSetTest_PiOver2Parser implements PHPUnit_Extensions_Database_DataSet_IYamlParser {
    public function parseYaml($yamlFile) {
        return array('math_table' =>
            array(
                array('answer' => 'pi/2')));
    }
}
