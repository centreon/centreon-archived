<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\LdapConfigurationListingPage;
use Centreon\Test\Behat\Administration\LdapConfigurationPage;

class LdapConfigurationContext extends CentreonContext
{
    
    private $page;

        

    /**
     * @When I add a new LDAP configuration
     */
    public function iAddANewLdapConfiguration()
    {
       $this->page = new LdapConfigurationListingPage($this);
       $this->assertFind('css', 'a[class="btc bt_success"]')->click();
       $this->page = new LdapConfigurationPage($this);
       $this->page->setProperties(array(
           'configuration_name' => 'ldap_acceptance_test',
           'description' => 'an ldap configuration test',
           'template' => 'Posix'
       ));
       $this->page->save();
       throw new Exception('not found');
       
    }

    /**
     * @Then the LDAP configuration is saved with its properties
     */
    public function theLdapConfigurationIsSavedWithItsProperties()
    {
       //$this->page->save();
       //throw new Exception('not found');
    }

    /**
     * @Given an existing LDAP configuration
     */
    public function anExistingLdapConfiguration()
    {
        
    }

    /**
     * @When I duplicate the LDAP configuration
     */
    public function iDuplicateTheLdapConfiguration()
    {
        throw new PendingException();
    }

    /**
     * @Then name is automatically incremented
     */
    public function nameIsAutomaticallyIncremented()
    {
        
    }

    /**
     * @Then other properties are the same than in the model
     */
    public function otherPropertiesAreTheSameThanInTheModel()
    {
        
    }

    /**
     * @When I modify some properties of an existing LDAP configuration
     */
    public function iModifySomePropertiesOfAnExistingLdapConfiguration()
    {
        
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
