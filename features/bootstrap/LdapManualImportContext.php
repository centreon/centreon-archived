<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\LdapConfigurationListingPage;
use Centreon\Test\Behat\Administration\LdapConfigurationPage;

class LdapManualImportContext extends CentreonContext
{
    protected $page;
    protected $configuration_name ='ldapacceptancetest';
    
    
     /**
     * @Given a LDAP configuration has been created
     */
    public function aLdapConfigurationHasBeenCreated()
    {
        $this->launchCentreonWebContainer('web_openldap');
        $this->iAmLoggedIn();
        
        $this->page = new LdapConfigurationListingPage($this);
        $this->page = $this->page->inspect('openldap');
        $this->page->setProperties(array(
           'enable_authentification' => 1,
           'auto_import' => 0,
       ));
        
        $this->page->save();
        /*throw new Exception('notfound');
        //throw new Exception('notfound');
        
        /*$this->page = new LdapConfigurationPage($this);
        $this->page->setProperties(array(
           'configuration_name' => $this->configuration_name,
           'description' => 'an ldap Manual import test',
           'enable_authentification' => 0,
           'auto_import' => 0,
           'template' => 'Posix'
       ));
        $this->page->save();*/
        
    }

    /**
     * @Given LDAP authentication is disabled
     */
    public function ldapAuthenticationIsDisabled()
    {
        $this->page = new LdapConfigurationListingPage($this);
        
        $object = $this->page->getEntry('openldap');
        var_dump($object);
        
        if ($object['status'] != 'Enabled') {
            throw new Exception(' LDAP authentification is disabled');
        }
        /*$this->page = $this->page->inspect('openldap');
        $this->page->setProperties(array(
           'enable_authentification' => 0,
           'auto_import' => 0,
       ));
        throw new Exception('notfound');*/
        //$this->page->save();
        //throw new Exception('notfound');
        
        
    }

    /**
     * @Given users auto import is disabled
     */
    public function usersAutoImportIsDisabled()
    {
        $this->page = new LdapConfigurationListingPage($this);
        $this->assertFindLink('openldap')->click();
        $value = $this->assertFind('css', 'input[name="ldap_auto_import[ldap_auto_import]"]')->getValue();
        
        if ($value != 0) {
            throw new Exception('Users auto import enabled');
        }
        
    }

    /**
     * @Given I search a specific user whose alias contains a special character such as an accent
     */
    public function iSearchASpecificUserWhoseAliasContainsASpecialCharacterSuchAsAnAccent()
    {
        $this->page = new LdapConfigurationListingPage($this);
        $this->assertFindLink('openldap')->click();
        sleep(10);
        $this->assertFindButton('Import users manually')->click();
        sleep(10);
        $this->assertFindButton('Search')->click();
        sleep(10);
        throw new Exception('notfound');
        
    }

    /**
     * @Given the LDAP search result displays the expected alias
     */
    public function theLdapSearchResultDisplaysTheExpectedAlias()
    {
        
    }

    /**
     * @When I import the user
     */
    public function iImportTheUser()
    {
        
    }

    /**
     * @Then the user is created
     */
    public function theUserIsCreated()
    {
        
    }

    /**
     * @Given one alias with an accent has been manually imported
     */
    public function oneAliasWithAnAccentHasBeenManuallyImported()
    {
        
    }

    /**
     * @When this user logins to Centreon Web
     */
    public function thisUserLoginsToCentreonWeb()
    {
        
    }

    /**
     * @Then he's logged by default on Home page
     */
    public function hesLoggedByDefaultOnHomePage()
    {
        
    }

}
