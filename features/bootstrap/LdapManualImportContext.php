<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\LdapConfigurationListingPage;
use Centreon\Test\Behat\Administration\LdapUserImportPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\LoginPage;

class LdapManualImportContext extends CentreonContext
{
    protected $page;
    protected $alias = 'centréon-ldap4';

    /**
     * @Given a LDAP configuration with Users auto import disabled has been created
     */
    public function aLdapConfigurationWithUsersAutoImportDisabledHasBeenCreated()
    {
        $this->page = new LdapConfigurationListingPage($this);
        $this->page = $this->page->inspect('OpenLDAP');
        $this->page->setProperties(array(
            'enable_authentication' => 1,
            'auto_import' => 0,
        ));
        $this->page = $this->page->save();
        $this->page = $this->page->inspect('OpenLDAP');
        if ($this->page->getProperty('auto_import') != 0) {
            throw new Exception('Users auto import enabled');
        }
    }

    /**
     * @Given I search a specific user whose alias contains a special character such as an accent
     */
    public function iSearchASpecificUserWhoseAliasContainsASpecialCharacterSuchAsAnAccent()
    {
        $this->page = new LdapUserImportPage($this);
        $this->page->setProperties(
            array(
                'servers' => array(
                    'OpenLDAP' => array(
                        'checked' => true
                    )
                )
            )
        );
    }

    /**
     * @Given the LDAP search result displays the expected alias
     */
    public function theLdapSearchResultDisplaysTheExpectedAlias()
    {
        $this->assertFindButton('Search')->click();
    }

    /**
     * @When I import the user
     */
    public function iImportTheUser()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[id^="contact_alias"][value="centréon-ldap4"]'
                );
            },
            'user to import not found.',
            10
        );
        $line = $this->assertFind(
            'css',
            'input[id^="contact_alias"][value="centréon-ldap4"]'
        )->getParent()->getParent();
        $this->assertFindIn($line, 'css', 'input[type="checkbox"]')->click();
        $this->assertFindButton('submitA')->click();
    }

    /**
     * @Then the user is created
     */
    public function theUserIsCreated()
    {
        $this->assertFindLink('centréon-ldap4')->click();
        $this->page = new ContactConfigurationListingPage($this);
        $object = $this->page->getEntry($this->alias);
        if ($object['alias'] != $this->alias) {
            throw new Exception(' contact not created ');
        }
    }

    /**
     * @Given one alias with an accent has been manually imported
     */
    public function oneAliasWithAnAccentHasBeenManuallyImported()
    {
        $this->aLdapConfigurationWithUsersAutoImportDisabledHasBeenCreated();
        $this->iSearchASpecificUserWhoseAliasContainsASpecialCharacterSuchAsAnAccent();
        $this->theLdapSearchResultDisplaysTheExpectedAlias();
        $this->iImportTheUser();
    }

    /**
     * @When this user logins to Centreon Web
     */
    public function thisUserLoginsToCentreonWeb()
    {
        $this->iAmLoggedOut();
        $this->page = new LoginPage($this);
        $this->page->login($this->alias, 'centreon-ldap4');
    }

    /**
     * @Then he's logged by default on Home page
     */
    public function hesLoggedByDefaultOnHomePage()
    {
        if (!$this->assertFind('css', 'nav#sidebar')) {
            throw new Exception('The user is not logged in');
        }
    }
}
