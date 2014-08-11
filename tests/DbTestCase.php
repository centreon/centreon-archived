<?php

namespace Test\Centreon;

use \Centreon\Internal\Db,
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

    public static function setUpBeforeClass()
    {
        $bootstrapSteps = array('configuration', 'database');
        $bootstrap = new Bootstrap();
        $bootstrap->init($bootstrapSteps);
        self::installTables();
    }

    public static function tearDownAfterClass()
    {
        self::dropTables();
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
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);

        \PropelSQLParser::executeString($strDiff, $db);
    }

    protected function loadDatas()
    {
        $db = Di::getDefault()->get('db_centreon');
        /* Load from file */
        $sql = file_get_contents(__DIR__ . "/data/sql/load.sql");
        \PropelSQLParser::executeString($sql, $db);
    }

    protected function truncateDatas()
    {
        $db = Di::getDefault()->get('db_centreon');
        /* Set foreign keys no check */
        /* @todo work with other database engine */
        $strTruncate = "SET foreign_key_checks = 0;\n";
        foreach (DbTestCase::$tables as $table) {
            $strTruncate .= "TRUNCATE TABLE " . $table->getName() . ";\n";
        }
        $strTruncate .= "SET foreign_key_checks = 1;\n";
        \PropelSQLParser::executeString($strTruncate, $db);
    }
}

