<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Centreon\Core;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Template extends \Smarty
{
    /**
     *
     * @var string 
     */
    private $templateFile;
    
    /**
     *
     * @var array 
     */
    private $cssResources;
    
    /**
     *
     * @var array 
     */
    private $jsResources;
    
    /**
     *
     * @var array 
     */
    private $exclusionList;
    
    /**
     * 
     * @param string $newTemplateFile
     */
    public function __construct($newTemplateFile = "", $enableCaching = 0)
    {
        $this->templateFile = $newTemplateFile;
        $this->caching = $enableCaching;
        
        $this->cssResources = array();
        $this->jsResources = array();
        $this->buildExclusionList();
        $this->initConfig();
        parent::__construct();
    }
    
    /**
     * 
     * @param type $config
     */
    public function initConfig()
    {
        $di = \Centreon\Core\Di::getDefault();
        $config = $di->get('config');
        
        $this->template_dir = $config->get('template', 'templateDir');
        $this->compile_dir = $config->get('template', 'compileDir');
        $this->config_dir = $config->get('template', 'configDir');
        $this->cache_dir = $config->get('template', 'cacheDir');
        
        $this->compile_check = true;
        $this->force_compile = true;
    }
    
    /**
     * @todo Maybe load this list from a config file
     */
    private function buildExclusionList()
    {
        $this->exclusionList = array(
            'cssFileList',
            'jsFileList'
        );
    }
    
    /**
     * 
     * @throws \Centreon\Exception If the template file is not defined
     */
    public function display()
    {
        if ($this->templateFile === "") {
            throw new Exception ("Template file missing", 404);
        }
        $this->loadResources();
        parent::display($this->templateFile);
    }
    
    /**
     * 
     */
    private function loadResources() {
        parent::assign('cssFileList', $this->cssResources);
        parent::assign('jsFileList', $this->jsResources);
    }
    
    /**
     * 
     * @param string $fileName CSS file to add
     */
    public function addCss($fileName)
    {
        if (!in_array($fileName, $this->cssResources)) {
            $this->cssResources[] = $fileName;
        }
        return $this;
    }
    
    /**
     * 
     * @param string $fileName Javascript file to add
     */
    public function addJs($fileName)
    {
        if (!in_array($fileName, $this->jsResources)) {
            $this->jsResources[] = $fileName;
        }
        return $this;
    }

    /**
     * 
     * @param string $varName Name of the variable to add
     * @param mixed $varValue Value of the variable to add
     * @throws \Centreon\Exception If variable name is reserved
     */
    public function assign($varName, $varValue)
    {
        if (in_array($varName, $this->exclusionList)) {
            throw new Exception('This variable name is reserved', 403);
        }
        parent::assign($varName, $varValue);
        return $this;
    }
}