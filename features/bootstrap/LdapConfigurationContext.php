<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\LdapConfigurationListingPage;
use Centreon\Test\Behat\Administration\LdapConfigurationPage;

class LdapConfigurationContext extends CentreonContext
{
    protected $page;
    protected $configuration_name = 'ldapacceptancetest';
    protected $newConfigurationName = 'the configuration name has been changed';


    /**
     * @When I add a new LDAP configuration
     */
    public function iAddANewLdapConfiguration()
    {
        $this->page = new LdapConfigurationPage($this);
        $this->page->setProperties(array(
            'configuration_name' => $this->configuration_name,
            'description' => 'an ldap configuration test',
            'enable_authentication' => 1,
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
        $this->iAddANewLdapConfiguration();
        $this->page->save();
        $this->page = new LdapConfigurationListingPage($this);
        $this->page = $this->page->inspect($this->configuration_name);
        $this->page->setProperties(array('configuration_name' => $this->newConfigurationName));
        $this->page->save();
    }

    /**
     * @Then all changes are saved
     */
    public function allChangesAreSaved()
    {
        $this->page = new LdapConfigurationListingPage($this);
        $object = $this->page->getEntry($this->newConfigurationName);

        if ($object['configuration_name'] != $this->newConfigurationName) {
            throw new \Exception('the Configuration has not changed.');
        }
    }

    /**
     * @When I have deleted one existing LDAP configuration
     */
    public function iHaveDeletedOneExistingLdapConfiguration()
    {
        $this->iAddANewLdapConfiguration();
        $this->page->save();
        $this->page = new LdapConfigurationListingPage($this);
        $object = $this->page->getEntry($this->configuration_name);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->page->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then this configuration has disappeared from the LDAP configuration list
     */
    public function thisConfigurationHasDisappearedFromTheLdapConfigurationList()
    {
        $this->page = new LdapConfigurationListingPage($this);
        $object = $this->page->getEntries();

        if (isset($object[$this->configuration_name])) {
            throw new \Exception('the Configuration is not deleted.');
        }
    }
}
