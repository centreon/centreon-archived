<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class ContactConfigurationContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'name' => 'contactName',
        'alias' => 'contactAlias',
        'email' => 'contact@localhost',
        'password' => 'contactpwd',
        'password2' => 'contactpwd',
        'admin' => 0,
        'dn' => 'contactDN',
        'host_notification_period' => 'workhours',
        'service_notification_period' => 'nonworkhours'
    );
    private $updatedProperties = array(
        'name' => 'modifiedName',
        'alias' => 'modifiedAlias',
        'email' => 'modified@localhost',
        'admin' => 1,
        'dn' => 'modifiedDn',
        'host_notification_period' => 'workhours',
        'service_notification_period' => 'nonworkhours'
    );

    /**
     * @Given a contact
     */
    public function aContactIsConfigured()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I update contact properties
     */
    public function iUpdateContactProperties()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['alias']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the contact properties are updated
     */
    public function theContactPropertiesAreUpdated()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->updatedProperties['alias']);
        $object = $this->currentPage->getProperties();
        $tableau = array();
        foreach($this->updatedProperties as $key => $value) {
            if ($value != $object[$key]) {
                $tableau[] =
                    $key . ' (got ' . $object[$key] .
                    ', expected ' . $value . ')';
            }
        }
        if (count($tableau) > 0) {
            throw new \Exception("Some properties are not being updated : " . implode(',', $tableau));
        }
    }
}
