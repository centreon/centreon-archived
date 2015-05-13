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

namespace Centreon\Internal\Database;

use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Filesystem\Directory;

/**
 * Description of PropelMigration
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class PropelMigration
{
    /**
     *
     * @var type 
     */
    protected $di;
    
    /**
     *
     * @var type 
     */
    protected $appConfig;
    
    /**
     *
     * @var type 
     */
    protected $targetDb = 'db_centreon';
    
    /**
     *
     * @var type 
     */
    protected $propelConfiguration = array();
    
    /**
     *
     * @var type 
     */
    protected $tmpDir;
    
    /**
     *
     * @var type 
     */
    protected $propelPath;
    
    /**
     *
     * @var type 
     */
    protected $appPath;
    
    /**
     *
     * @var \Centreon\Internal\Database\SchemaBuilder 
     */
    protected $mySchemaBuilder;


    /**
     * 
     */
    public function __construct()
    {
        $this->di = Di::getDefault();
        $this->appConfig = $this->di->get('config');
        $this->propelConfiguration['datasources'] = array('centreon' => array(
            'adapter' => 'mysql',
            'connection' => array(
                'dsn' => $this->appConfig->get($this->targetDb, 'dsn'),
                'user' => $this->appConfig->get($this->targetDb, 'username'),
                'password' => $this->appConfig->get($this->targetDb, 'password')
            )
        ));
        
        $this->appPath = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        
        $this->tmpDir = rtrim($this->appConfig->get('global', 'centreon_generate_tmp_dir'), '/') . '/centreon/propel';
        if (file_exists($this->tmpDir)) {
            Directory::delete($this->tmpDir, true);
        }
        mkdir($this->tmpDir, 0700, true);
        mkdir($this->tmpDir . '/schema/', 0700, true);
        $this->propelPath = $this->appPath . '/vendor/propel/propel1/';
        
        $this->mySchemaBuilder = new SchemaBuilder('centreon', $this->tmpDir . '/schema/');
    }
    
    /**
     * 
     */
    public function runPhing($taskName)
    {
        // Copy Files
        $this->mySchemaBuilder->loadXmlFiles();
        
        // Create build.properties file
        $this->createBuildPropertiesFile($this->tmpDir.'/build.properties');
        
        // Create buildtime-conf file
        $this->createBuildTimeConfFile($this->tmpDir.'/buildtime-conf.xml');
        
        //
        $args = array();
        $args = $this->getPhingArguments();
        $args[] = $taskName;
        
        // Enable output buffering
        \Phing::setOutputStream(new \OutputStream(fopen('php://output', 'w')));
        \Phing::setErrorStream(new \OutputStream(fopen('php://output', 'w')));
        \Phing::startup();
        \Phing::setProperty('phing.home', getenv('PHING_HOME'));
        
        //
        $myPhing = new \Phing();
        //$returnStatus = true;
        
        $myPhing->execute($args);
        $myPhing->runBuild();
        /*$this->buffer = ob_get_contents();
        // Guess errors
        if (strstr($this->buffer, 'failed. Aborting.') ||
            strstr($this->buffer, 'Failed to execute') ||
            strstr($this->buffer, 'failed for the following reason:')) {
        }*/
    }
    
    /**
     * 
     * @param type $output
     */
    protected function createBuildPropertiesFile($output)
    {
        $source = $this->appPath . '/core/custom/Propel/build.properties';
        if (file_exists($source)) {
            copy($source, $output);
        }
    }
    
    /**
     * 
     * @param type $output
     */
    protected function createBuildTimeConfFile($output)
    {
        /*$container = $this->getContainer();
        if (!$container->has('propel.configuration')) {
            throw new \InvalidArgumentException('Could not find Propel configuration.');
        }*/
        $xml = strtr(<<<EOT
<?xml version="1.0"?>
<config>
  <propel>
    <datasources default="%default_connection%">
EOT
        , array('%default_connection%' => $this->targetDb));
        foreach ($this->propelConfiguration['datasources'] as $name => $datasource) {
            $xml .= strtr(<<<EOT
      <datasource id="%name%">
        <adapter>%adapter%</adapter>
        <connection>
          <dsn>%dsn%</dsn>
          <user>%username%</user>
          <password>%password%</password>
        </connection>
      </datasource>
EOT
            , array(
                '%name%'     => $name,
                '%adapter%'  => $datasource['adapter'],
                '%dsn%'      => $datasource['connection']['dsn'],
                '%username%' => $datasource['connection']['user'],
                '%password%' => isset($datasource['connection']['password']) ? $datasource['connection']['password'] : '',
            ));
        }
        $xml .= <<<EOT
    </datasources>
  </propel>
</config>
EOT;
        file_put_contents($output, $xml);
    }
    
    /**
     * Compiles arguments/properties for the Phing process.
     * @return array
     */
    private function getPhingArguments($properties = array())
    {
        $args = array();
        
        // Default properties
        $properties = array_merge(
            array(
                'propel.database'           => 'mysql',
                'propel.project'            => 'centreon',
                'propel.targetPackage'      => 'centreon',
                'project.dir'               => $this->tmpDir . '/',
                'propel.output.dir'         => $this->tmpDir . '/output/',
                'propel.schema.dir'         => $this->tmpDir . '/schema/',
                'propel.php.dir'            => $this->tmpDir . '/generate/',
                'propel.packageObjectModel' => true,
                'propel.useDateTimeClass'   => true,
                'propel.dateTimeClass'      => 'DateTime',
                'propel.defaultTimeFormat'  => '',
                'propel.defaultDateFormat'  => '',
                'propel.addClassLevelComment'       => false,
                'propel.defaultTimeStampFormat'     => '',
                'propel.builder.pluralizer.class'   => 'builder.util.StandardEnglishPluralizer',
            ),
            $properties
        );
        
        // 
        foreach ($properties as $key => $value) {
            $args[] = "-D$key=$value";
        }
        
        // Build file
        $args[] = '-f';
        $args[] = realpath($this->propelPath.'/generator/build.xml');
        return $args;
    }
}
