<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\LdapConfigurationListingPage;
use Centreon\Test\Behat\Administration\LdapConfigurationPage;

class LdapConfigurationContext extends CentreonContext
{   
    protected $page;
    protected $configuration_name ='ldapacceptancetest';
    
   /**
     * @When I add a new LDAP configuration
     */
    public function iAddANewLdapConfiguration()
    {
       $this->page = new LdapConfigurationPage($this);
       $this->page->setProperties(array(
           'configuration_name' => $this->configuration_name,
           'description' => 'an ldap configuration test',
           'enable_authentification' => 1,
           'template' => 'Posix'
       ));
      
    }
    
    
    /**
     * @Then the LDAP configuration is saved with its properties
     */
    public function theLdapConfigurationIsSavedWithItsProperties()
    {
         $this->page->save();
    }

    /**
     * @When I modify some properties of an existing LDAP configuration
     */
    public function iModifySomePropertiesOfAnExistingLdapConfiguration()
    {
        //$this->page->iAddANewLdapConfiguration();
        // new Exception('not found');
        //$this->page = $this->page->getEntries();
       // var_dump($this->page);
        //$this->page->setProperties(array('description' => 'A new description'));
        //throw new Exception('not found');
        //$this->page->save();
        //throw new Exception('not found');*/
    }

    /**
     * @Then all changes are saved
     */
    public function allChangesAreSaved()
    {
        
    }

    /**
     * @When I have deleted one existing LDAP configuration
     */
    public function iHaveDeletedOneExistingLdapConfiguration()
    {
        
    }

    /**
     * @Then this configuration has disappeared from the LDAP configuration list
     */
    public function thisConfigurationHasDisappearedFromTheLdapConfigurationList()
    {
        
    }

}
