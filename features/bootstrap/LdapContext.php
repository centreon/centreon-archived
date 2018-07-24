<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\Administration\ACLMenuConfigurationPage;
use Centreon\Test\Behat\Administration\ACLActionConfigurationPage;
use Centreon\Test\Behat\Administration\ACLResourceConfigurationPage;

/**
 * Features context.
 */
class LdapContext extends CentreonContext
{
    private $page;

    /**
     * @Given a ldap user has been imported
     */
    public function aLdapUserHasBeenImported()
    {
        $this->configureAclForLdapContacts();

        $this->iAmLoggedOut();

        $this->parameters['centreon_user'] = 'centreon-ldap';
        $this->parameters['centreon_password'] = 'centreon-ldap';

        $this->iAmLoggedIn();
    }

    /**
     * @When I am on the ldap contact page with a non admin user
     */
    public function IAmOnTheLdapContactPageWithANonAdminUser()
    {
        $this->page = new ContactConfigurationListingPage($this);
        $this->page = $this->page->inspect('centreon-ldap');
    }

    /**
     * @Then I cannot update the contact dn
     */
    public function ICannotUpdateTheContactDN()
    {
        if ($this->assertFind('css', 'input[name="contact_ldap_dn"]')->getAttribute('type') != 'hidden') {
            throw new \Exception('Contact ldap dn can be updated.');
        }
    }

    /**
     * @Then I cannot update the contact password
     */
    public function ICannotUpdateTheContactPassword()
    {
        if ($this->getSession()->getPage()->has('css', 'input#paswd1')) {
            throw new \Exception('Contact password is displayed.');
        }
    }

    /**
     * Configure ldap acls to access all objects
     */
    public function configureAclForLdapContacts()
    {
        $aclGroupProperties = array(
            'group_name' => 'ldap',
            'group_alias' => 'ldap',
            'contactgroups' => array(
                'linux (LDAP : OpenLDAP)',
                'windows (LDAP : OpenLDAP)',
                'networking (LDAP : OpenLDAP)'
            )
        );

        $this->page = new ACLGroupConfigurationPage($this);
        $this->page->setProperties($aclGroupProperties);
        $this->page->save();

        $aclMenuProperties = array(
            'acl_name' => 'ldap',
            'acl_alias' => 'ldap',
            'acl_groups' => array(
                'ldap'
            )
        );

        $this->page = new ACLMenuConfigurationPage($this);
        $this->page->setProperties($aclMenuProperties);
        $this->page->selectAll();
        $this->page->save();

        $aclResourceProperties = array(
            'acl_name' => 'ldap',
            'acl_alias' => 'ldap',
            'acl_groups' => array(
                'ldap'
            )
        );

        $this->page = new ACLResourceConfigurationPage($this);
        $this->page->setProperties($aclResourceProperties);
        $this->page->selectAll();
        $this->page->save();

        $aclActionProperties = array(
            'acl_name' => 'ldap',
            'acl_alias' => 'ldap',
            'acl_groups' => array(
                'ldap'
            )
        );
        $this->page = new ACLActionConfigurationPage($this);
        $this->page->setProperties($aclActionProperties);
        $this->page->selectAll();
        $this->page->save();
    }
}
