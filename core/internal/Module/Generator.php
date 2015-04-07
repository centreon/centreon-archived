<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace Centreon\Internal\Module;

use Centreon\Internal\Di;
use Centreon\Internal\Informations as CentreonInformations;

/**
 * Module Generator
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Generator
{
    /**
     *
     * @var type 
     */
    private $moduleDisplayName;
    
    /**
     *
     * @var type 
     */
    private $moduleCanonicalName;
    
    /**
     *
     * @var type 
     */
    private $moduleShortName;
    
    /**
     *
     * @var type 
     */
    private $moduleAuthor;
    
    /**
     *
     * @var type 
     */
    private $moduleFolderPath;
    
    /**
     *
     * @var type 
     */
    private $licensePath;
    
    /**
     *
     * @var type 
     */
    private $moduleFolderStructure;
    
    /**
     * 
     * @param type $moduleCanonicalName
     */
    public function __construct($moduleCanonicalName)
    {
        $this->moduleCanonicalName = $moduleCanonicalName;
        $config = Di::getDefault()->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        $this->licensePath = $centreonPath . '/infos/header.txt';
        $this->moduleFolderPath = $centreonPath . '/modules/' . $moduleCanonicalName . 'Module';
        $this->defineModuleFolderStructure();
    }
    
    /**
     * 
     */
    public function defineModuleFolderStructure()
    {
        $this->moduleFolderStructure= array(
            'api' => $this->moduleFolderPath . '/api',
            'apiInternal' => $this->moduleFolderPath . '/api/internal',
            'apiRest' => $this->moduleFolderPath . '/api/rest',
            'apiSoap' => $this->moduleFolderPath . '/api/soap',
            'commands' => $this->moduleFolderPath . '/commands',
            'config' => $this->moduleFolderPath . '/config',
            'controllers' => $this->moduleFolderPath . '/controllers',
            'customs' => $this->moduleFolderPath . '/customs',
            'events' => $this->moduleFolderPath . '/events',
            'install' => $this->moduleFolderPath . '/install',
            'installDb' => $this->moduleFolderPath . '/install/db',
            'installDbCentreon' => $this->moduleFolderPath . '/install/db/centreon',
            'forms' => $this->moduleFolderPath . '/forms',
            'internal' => $this->moduleFolderPath . '/internal',
            'models' => $this->moduleFolderPath . '/models',
            'repositories' => $this->moduleFolderPath . '/repositories',
            'tests' => $this->moduleFolderPath . '/tests',
            'views' => $this->moduleFolderPath . '/views'
        );
    }
    
    /**
     * 
     * @param type $shortname
     */
    public function setModuleShortName($shortname)
    {
        $this->moduleShortName = $shortname;
    }
    
    /**
     * 
     * @param type $displayName
     */
    public function setModuleDisplayName($displayName)
    {
        $this->moduleDisplayName = $displayName;
    }
    
    /**
     * 
     * @param type $author
     */
    public function setModuleAuthor($author)
    {
        $this->moduleAuthor = $author;
    }
    
    /**
     * 
     */
    public function generateConfigFile()
    {
        $moduleConfig = array(
            'name' => $this->moduleDisplayName,
            'shortname' => $this->moduleShortName,
            'version' => '1.0.0',
            'author' => array($this->moduleAuthor),
            'isuninstallable' => 1,
            'isdisableable' => 1,
            'url' => "",
            'description' => $this->moduleDisplayName,
            'core version' => CentreonInformations::getCentreonVersion(),
            'dependencies' => array(
                array(
                    'name' => 'centreon-administration',
                    'version' => CentreonInformations::getCentreonVersion()
                )
            ),
            'optionnal dependencies' => array(),
            'php module dependencies' => array(),
            'program dependencies' => array()
        );
        
        file_put_contents(
            $this->moduleFolderStructure['install'] . '/config.json',
            json_encode($moduleConfig, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)
        );
    }
    
    /**
     * 
     */
    public function generateModuleStructure()
    {
        mkdir($this->moduleFolderPath, 0777, true);
        foreach ($this->moduleFolderStructure as $subFolder) {
            mkdir($subFolder, 0777, true);
        }
    }
    
    /**
     * 
     * @param type $withLicense
     */
    public function createSampleInstaller($withLicense = true)
    {
        $installerClass = "<?php\n\n";
        if ($withLicense) {
            $installerClass .= file_get_contents($this->licensePath);
        }
        $installerClass .= "namespace $this->moduleCanonicalName\Install;\n\n";
        $installerClass .= "class Installer extends \Centreon\Internal\Module\Installer\n";
        $installerClass .= "{\n";
        $installerClass .= $this->indent() . 'public function __construct($moduleDirectory, $moduleInfo)' . "\n";
        $installerClass .= $this->indent() . "{\n";
        $installerClass .= $this->indent(2) . 'parent::__construct($moduleDirectory, $moduleInfo);';
        $installerClass .= $this->indent() . "\n" . $this->indent() . "}\n\n";
        $installerClass .= $this->indent() . 'public function customPreInstall()' . "\n";
        $installerClass .= $this->indent() . "{\n" . $this->indent(2) . "\n" . $this->indent() . "}\n\n";
        $installerClass .= $this->indent() . 'public function customInstall()' . "\n";
        $installerClass .= $this->indent() . "{\n" . $this->indent(2) . "\n" . $this->indent() . "}\n\n";
        $installerClass .= $this->indent() . 'public function customRemove()' . "\n";
        $installerClass .= $this->indent() . "{\n" . $this->indent(2) . "\n" . $this->indent() . "}\n";
        $installerClass .= "}\n\n";
        file_put_contents($this->moduleFolderStructure['install'] . '/Installer.php', $installerClass);
    }
    
    /**
     * 
     * @param type $withLicense
     */
    public function createSampleController($withLicense = true)
    {
        $controllerClass = "<?php\n\n";
        if ($withLicense) {
            $controllerClass .= file_get_contents($this->licensePath);
        }
        $controllerClass .= "namespace $this->moduleCanonicalName\Controllers;\n\n";
        $controllerClass .= "class SampleController extends \Centreon\Internal\Controller\n";
        $controllerClass .= "{\n";
        $controllerClass .= $this->indent() . 'public static $moduleName = ' . "'$this->moduleCanonicalName';\n\n";
        $controllerClass .= $this->indent() . "/**\n";
        $controllerClass .= $this->indent() . " * @method get\n";
        $controllerClass .= $this->indent() . " * @route /sample\n";
        $controllerClass .= $this->indent() . " */\n";
        $controllerClass .= $this->indent() . "public function sampleAction()\n";
        $controllerClass .= $this->indent() . "{\n";
        $controllerClass .= $this->indent(2) . '$this->assignVarToTpl(\'centreonVersion\', \'Centreon 3.0\');' . "\n";
        $controllerClass .= $this->indent(2) . '$this->display("sample.tpl");' . "\n";
        $controllerClass .= $this->indent() . "}\n}\n";
        $controllerClass .= "\n";
        file_put_contents($this->moduleFolderStructure['controllers'] . '/SampleController.php', $controllerClass);
    }
    
    /**
     * 
     */
    public function createSampleView()
    {
        $viewContent = '{extends file="file:[Core]viewLayout.tpl"}' . "\n\n";
        $viewContent .= '{block name="title"}Sample{/block}' . "\n\n";
        $viewContent .= '{block name="content"}Welcome to my {$centreonVersion} module{/block}' . "\n\n";
        file_put_contents($this->moduleFolderStructure['views'] . '/sample.tpl', $viewContent);
    }
    
    /**
     * 
     * @param type $occurence
     * @return string
     */
    private function indent($occurence = 1)
    {
        $finalIndent = "";
        for ($i=0; $i<$occurence; $i++) {
            $finalIndent .= "    ";
        }
        return $finalIndent;
    }
}
