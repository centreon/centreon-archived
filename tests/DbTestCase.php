<?php

namespace Test\Centreon;

/**
 *
 * @todo use mysql
 */
class DbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private $conn = null;
    private $db = null;

    public function setUp()
    {
        $this->getConnection();
        $this->installTables();
    }

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if (is_null($this->conn)) {
            $this->db = new \Centreon\Internal\Db('sqlite::memory:');
            $di = new \Centreon\Internal\Di();
            \Centreon\Internal\Di::getDefault()->setShared('db_centreon', $this->db);
            $this->conn = $this->createDefaultDBConnection($this->db, ':memory:');
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

    /**
     * Install tables
     */
    protected function installTables()
    {
        $configParams = array(
            'propel.project' => 'centreon',
            'propel.database' => 'sqlite',
            'propel.database.url' => 'sqlite::memory:'
        );
    
        // Initilize Schema Parser
        $propelDb = new \SqliteSchemaParser($this->db);
        $propelDb->setGeneratorConfig(new \GeneratorConfig($configParams));

        $platform = new \SqlitePlatform($this->db); 


        // get Current Db State
        $currentDbAppData = new \AppData($platform);
        $currentDbAppData->setGeneratorConfig(new \GeneratorConfig($configParams));
        $currentDb = $currentDbAppData->addDatabase(array('name' => ':memory:'));
        $propelDb->parse($currentDb);
        
        // Retreive target DB State
        $updatedAppData = new \AppData($platform);


        // Get xml files
        $xmlDbFiles = glob(realpath(rtrim(CENTREON_PATH, '/') . '/install/db').'/*/*.xml');
        $xmlDbFiles = array_merge(
            $xmlDbFiles,
            glob(realpath(rtrim(CENTREON_PATH, '/') . '/modules') . '/*Module/install/db/*/*.xml')
        );

         // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new \SqlitePlatform($this->db), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $updatedAppData->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new \SqlitePlatform($this->db), null, 'utf-8');
        }
        unset($appDataObject);

        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        
        \PropelSQLParser::executeString($strDiff, $this->db);
    }
}

