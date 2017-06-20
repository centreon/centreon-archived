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
       throw new Exception('not found');
    }

    /**
     * @Then the LDAP configuration is saved with its properties
     */
    public function theLdapConfigurationIsSavedWithItsProperties()
    {
       
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
