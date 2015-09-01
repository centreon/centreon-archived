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


namespace Test\Centreon;

use Centreon\Internal\Install\Db,
    \Centreon\Internal\Di,
    \Centreon\Internal\Bootstrap,
    \Centreon\Custom\Propel\CentreonMysqlPlatform;

/**
 *
 * @todo use mysql
 */
class DbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    protected static $config = null;
    protected static $tables = array();
    protected $conn = null;
    protected $dataPath = null;
    protected static $bootstrapExtraSteps = array();

    public static function setUpBeforeClass()
    {
        $bootstrapSteps = array(
            'configuration', 
            'database', 
            'cache', 
            'routes',
            'organization'
        );
        $bootstrap = new Bootstrap();
        $bootstrap->init($bootstrapSteps);
        self::dropTables();
        self::installTables();
        $bootstrap->init(static::$bootstrapExtraSteps);
    }

    public static function tearDownAfterClass()
    {
        self::$tables = array();
        Di::reset();
    }

    public function setUp()
    {
        /* Load data into databases */
        $this->loadDatas();
    }

    public function tearDown()
    {
        /* Truncate all data */
        $this->truncateDatas();
    }

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if (is_null($this->conn)) {
            $db = Di::getDefault()->get('db_centreon');
            $this->conn = $this->createDefaultDBConnection($db, 'centreon');
        }
        return $this->conn;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $fixtures = glob(realpath(rtrim(CENTREON_PATH, '/') . '/core/tests/fixtures/*.xml'));
        $fixtures = array_merge(
            $fixtures,
            glob(realpath(rtrim(CENTREON_PATH, '/') . '/modules') . '/*Module/tests/fixtures/*.xml')
        );

        $compositeDs = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet();
        foreach ($fixtures as $fixture) {
            $ds = $this->createFlatXmlDataSet($fixture);
            $compositeDs->addDataset($ds);
        }
        return $compositeDs;
    }

    protected static function initializeCurrentSchema($platform)
    {
        $configParams = array(
            'propel.project' => 'centreon',
            'propel.database' => 'mysql',
            'propel.database.driver' => 'mysql',
            'propel.database.createUrl' => 'mysql://root@localhost/',
            'propel.database.url' => 'mysql:dbname=centreon;host=localhost',
            'propel.database.user' => 'root',
            'propel.database.password' => '',
            'propel.database.encoding' => 'utf-8'
        );
    
        // Initilize Schema Parser
        $db = Di::getDefault()->get('db_centreon');
        $propelDb = new \MysqlSchemaParser($db);
        $propelDb->setGeneratorConfig(new \GeneratorConfig($configParams));
        $propelDb->setPlatform($platform);

        // get Current Db State
        $currentDbAppData = new \AppData($platform);
        $currentDbAppData->setGeneratorConfig(new \GeneratorConfig($configParams));
        $currentDb = $currentDbAppData->addDatabase(array('name' => 'centreon'));
        $propelDb->parse($currentDb);

        return $currentDb;
    }

    /**
     * Install tables
     */
    protected function installTables()
    {
        $db = Di::getDefault()->get('db_centreon');
        $platform = new CentreonMysqlPlatform($db);

        // Get current DB State
        $currentDb = self::initializeCurrentSchema($platform);

        // Retreive target DB State
        $updatedAppData = new \AppData($platform);


        // Get xml files
        $xmlDbFiles = glob(realpath(rtrim(CENTREON_PATH, '/') . '/install/db').'/*/*.xml');
        $xmlDbFiles = array_merge(
            $xmlDbFiles,
            glob(realpath(rtrim(CENTREON_PATH, '/') . '/modules') . '/*Module/install/db/*/*.xml')
        );

         // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $updatedAppData->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        }
        unset($appDataObject);

        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);

        /* Get the list of tables */
        self::$tables = $updatedAppData->getDatabase('centreon')->getTables();
        
        \PropelSQLParser::executeString($strDiff, $db);
    }

    protected function dropTables()
    {
        $db = Di::getDefault()->get('db_centreon');
        $platform = new CentreonMysqlPlatform($db);

        // Get current DB State
        $currentDb = self::initializeCurrentSchema($platform);

        // Retreive target DB State
        $updatedAppData = new \AppData($platform);
        $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        $updatedAppData->joinAppDatas(array($appDataObject->parseFile(__DIR__ . '/data/empty.xml')));
        unset($appDataObject);
        
        /* @todo Fatorize */
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        if (false !== $diff) {
            $strDiff = $platform->getModifyDatabaseDDL($diff);
            $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
            \PropelSQLParser::executeString($strDiff, $db);
        }
    }

    protected function loadDatas()
    {
        /* Load from file */
        Db::loadDefaultDatas(__DIR__ . '/data/json/');
        if (false === is_null($this->dataPath)) {
            Db::loadDefaultDatas(CENTREON_PATH . $this->dataPath);
        }
    }

    /**
     * @todo truncate other db
     */
    protected function truncateDatas()
    {
        $db = Di::getDefault()->get('db_centreon');
        $db->query("SET foreign_key_checks = 0");
        $db->beginTransaction();
        foreach (DbTestCase::$tables as $table) {
            $db->exec("TRUNCATE TABLE " . $table->getName());
        }
        $db->commit();
        $db->query("SET foreign_key_checks = 1");
    }

    protected function tableEqualsXml($table, $xmlFile, $flatXml = false)
    {
        $method = $flatXml ? 'createFlatXmlDataSet' : 'createXmlDataSet';
        $dataset = $this->$method(
            $xmlFile
        )->getTable($table);
        $tableResult = $this->getConnection()->createQueryTable(
            $table,
            "SELECT * FROM $table"
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }
}

