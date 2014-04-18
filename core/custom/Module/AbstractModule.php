<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Centreon\Custom\Module;

/**
 * Description of AbstractModule
 *
 * @author lionel
 */
class AbstractModule implements iModuleInstaller
{
    protected $moduleInfo;
    
    protected $moduleDirectory;
    
    protected $moduleId;


    /**
     * 
     * @param type $moduleInfo
     */
    public function __construct($moduleDirectory, $moduleInfo)
    {
        $this->moduleInfo = $moduleInfo;
        $this->moduleDirectory = $moduleDirectory;
    }
    
    /**
     * 
     */
    public function install()
    {
        $this->preInstall();
        $this->installDb();
        $this->installForms();
        $this->installMenu();
        $this->customInstall();
        $this->postInstall();
    }
    
    public function customInstall()
    {
        
    }
    
    public function installMenu()
    {
        $filejson = $this->moduleDirectory . 'install/menu.json';
        if (file_exists($filejson)) {
            $menus = json_decode(file_get_contents($filejson), true);
            \Centreon\Internal\Module::parseMenuArray($menus);
        }
    }
    
    public function removeMenu()
    {
        
    }

    /**
     * @todo After seeing Propel
     */
    public function installDb()
    {
        
    }
    
    /**
     * @todo After seeing Propel
     */
    public function removeDb()
    {
        
    }
    
    /**
     * @todo Check for form dependencies
     */
    public function installForms()
    {
        $formsFiles = $this->moduleDirectory . '/install/forms/*.xml';
        foreach (glob($formsFiles) as $xmlFile) {
            \Centreon\Internal\Form\Installer::installFromXml($this->moduleId, $xmlFile);
        }
    }
    
    public function removeForms()
    {
        
    }
    
    /**
     * 
     */
    public function isDependenciesSatisfied()
    {
        $dependenciesSatisfied = true;
        $missingDependencies = array();
        foreach ($this->moduleInfo['dependencies'] as $module) {
            if (!\Centreon\Custom\Module\ModuleInformations::checkDependency($module)) {
                $dependenciesSatisfied = false;
                $missingDependencies[] = $module['name'];
            }
        }
        
        return array(
            'success' => $dependenciesSatisfied,
            'missingDependencies' => $missingDependencies
        );
    }
    
    public function preInstall()
    {
        $newModuleId = \Centreon\Models\Module::getIdByParameter('name', $this->moduleInfo['shortname']);
        if (count($newModuleId) == 0) {
            $params = array(
                'name' => $this->moduleInfo['shortname'],
                'alias' => $this->moduleInfo['name'],
                'description' => $this->moduleInfo['description'],
                'author' => implode(", ", $this->moduleInfo['author']),
                'name' => $this->moduleInfo['shortname'],
                'version' => $this->moduleInfo['version'],
                'isactivated' => '0',
                'isinstalled' => '0',
            );
            \Centreon\Models\Module::insert($params);
            $newModuleId = \Centreon\Models\Module::getIdByParameter('name', $this->moduleInfo['shortname']);
            $this->moduleId = $newModuleId[0];
        } else {
            throw new \Exception("Module already installed");
        }
    }
    
    /**
     * 
     */
    public function postInstall()
    {
        \Centreon\Models\Module::update($this->moduleId, array('isactivated' => '1','isinstalled' => '1'));
    }
    
    /**
     * 
     */
    public function remove()
    {
        $this->preRemove();
        $this->removeDb();
        $this->removeForms();
        $this->removeMenu();
        $this->postRemove();
    }
    
    public function customRemove()
    {
        
    }
    
    /**
     * 
     */
    public function preRemove()
    {
        $this->moduleId = $this->moduleInfo['id'];
    }
    
    /**
     * 
     */
    public function postRemove()
    {
        \Centreon\Models\Module::delete($this->moduleId);
    }
}
