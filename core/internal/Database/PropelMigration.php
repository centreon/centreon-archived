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
     * @var string 
     */
    protected $targetDb = 'db_centreon';
    
    /**
     *
     * @var array 
     */
    protected $propelConfiguration = array();
    
    /**
     *
     * @var string 
     */
    protected $tmpDir;
    
    /**
     *
     * @var string 
     */
    protected $outputDir;
    
    /**
     *
     * @var string 
     */
    protected $propelPath;
    
    /**
     *
     * @var string 
     */
    protected $appPath;
    
    /**
     *
     * @var \Centreon\Internal\Database\SchemaBuilder 
     */
    protected $mySchemaBuilder;
    
    /**
     *
     * @var string 
     */
    protected $module;


    /**
     * 
     * @param string $module
     */
    public function __construct($module = 'centreon')
    {
        $this->module = $module;
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
        
        $this->tmpDir = rtrim($this->appConfig->get('global', 'centreon_generate_tmp_dir'), '/')
            . '/'. $this->module . '/propel';
        if (file_exists($this->tmpDir)) {
            Directory::delete($this->tmpDir, true);
        }
        mkdir($this->tmpDir, 0700, true);
        mkdir($this->tmpDir . '/schema/', 0700, true);
        $this->propelPath = $this->appPath . '/vendor/propel/propel1/';
        
        $this->outputDir = $this->tmpDir . '/output/';
        
        $this->mySchemaBuilder = new SchemaBuilder('centreon', $this->tmpDir . '/schema/', $module);
    }
    
    /**
     * 
     * @param string $taskName
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
     * @param string $output
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
     * @param string $output
     */
    protected function createBuildTimeConfFile($output)
    {
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
     * 
     * @param string $outputDir
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;
    }
    
    /**
     * Compiles arguments/properties for the Phing process.
     * 
     * @param array $properties
     * @return array
     */
    private function getPhingArguments($properties = array())
    {
        $args = array();
        
        // Default properties
        $properties = array_merge(
            array(
                'propel.database'           => 'mysql',
                'propel.project'            => $this->module,
                'propel.targetPackage'      => $this->module,
                'project.dir'               => $this->tmpDir . '/',
                'propel.output.dir'         => $this->outputDir,
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
        $args[] = '-q';
        $args[] = '-f';
        $args[] = realpath($this->propelPath.'/generator/build.xml');
        return $args;
    }
}
