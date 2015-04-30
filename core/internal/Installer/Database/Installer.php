<?php

/*
 * Copyright 2005-2015 CENTREON
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
namespace Centreon\Internal\Installer\Database;

/**
 * 
 */
class Installer
{
    /**
     *
     * @var array 
     */
    private $propelConfigParams;
    
    /**
     *
     * @var type 
     */
    private $platform;
    
    /**
     *
     * @var type 
     */
    private $dbConnector;
    
    /**
     *
     * @var type 
     */
    private $propelDbConnector;
    
    /**
     * 
     */
    public function __construct()
    {
        ini_set('memory_limit', '-1');
        $di = Di::getDefault();
        $config = $di->get('config');
        
        $targetDb = 'db_centreon';
        $this->dbConnector = $di->get($targetDb);
        
        // Configuration for Propel
        $this->propelConfigParams = array(
            'propel.project' => 'centreon',
            'propel.database' => 'mysql',
            'propel.database.url' => $config->get($targetDb, 'dsn'),
            'propel.database.user' => $config->get($targetDb, 'username'),
            'propel.database.password' => $config->get($targetDb, 'password')
        );
        
        // Set the Current Platform and DB Connection
        $this->platform = new CentreonMysqlPlatform($this->dbConnector);
        
        // Initilize Schema Parser
        $this->propelDbConnector = new \MysqlSchemaParser($this->dbConnector);
        $this->propelDbConnector->setGeneratorConfig(new \GeneratorConfig($this->propelConfigParams));
        $this->propelDbConnector->setPlatform($this->platform);
        
    }
    
    /**
     * 
     */
    public function migrate()
    {
        // get Current Db State
        $currentDbAppData = new \AppData($this->platform);
        $currentDbAppData->setGeneratorConfig(new \GeneratorConfig($this->propelConfigParams));
        $currentDb = $currentDbAppData->addDatabase(array('name' => 'centreon'));
        $this->propelDbConnector->parse($currentDb);
        
        // Retreive target DB State
        $updatedAppData = new \AppData($this->platform);
        self::getDbFromXml($updatedAppData, 'centreon');
        
        // Get diff between current db state and target db state
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        $strDiff = $this->platform->getModifyDatabaseDDL($diff);
        file_put_contents("/tmp/installSqlLog.sql", $strDiff);
        //$sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        //unlink("/tmp/installSqlLog.sql");
        
        // Loading Modules Pre Update Operations
        self::preUpdate();
        
        // to sent to verify
        //$tablesToBeDropped = self::getTablesToBeRemoved($sqlToBeExecuted);
        
        // Perform Update
        \PropelSQLParser::executeString($strDiff, $this->dbConnector);
        
        // Loading Modules Post Update Operations
        self::postUpdate();
        
        // Empty Target DB
        self::deleteTargetDbSchema('centreon');
    }
}
