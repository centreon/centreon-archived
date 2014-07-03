<?php

namespace Centreon\Custom\Module;

/**
 * 
 */
interface iModuleInstaller
{
    /**
     * 
     */
    public function install();
    
    /**
     * 
     */
    public function customInstall();
    
    /**
     * 
     */
    public function customRemove();
    
    /**
     * 
     */
    public function installForms();
    
    /**
     * 
     */
    public function installDb();
    
    /**
     * 
     */
    public function removeDb();
    
    /**
     * 
     */
    public function isDependenciesSatisfied();
    
    /**
     * 
     */
    public function preInstall();
    
    /**
     * 
     */
    public function postInstall();
}
