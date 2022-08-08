<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;

class ContactConfigurationContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'name' => 'contactName',
        'alias' => 'contactAlias',
        'email' => 'contact@localhost',
        'pager' => 'contactPager',
        'contact_template' => 'contact_template',
        'contact_groups' => 'Supervisors',
        'notifications_enabled' => 1,
        'host_notify_on_down' => 0,
        'host_notify_on_unreachable' => 0,
        'host_notify_on_recovery' => 0,
        'host_notify_on_flapping' => 0,
        'host_notify_on_downtime_scheduled' => 0,
        'host_notify_on_none' => 1,
        'host_notification_period' => '24x7',
        'host_notification_command' => 'service-notify-by-email',
        'service_notify_on_none' => 0,
        'service_notify_on_warning' => 1,
        'service_notify_on_unknown' => 1,
        'service_notify_on_critical' => 1,
        'service_notify_on_recovery' => 1,
        'service_notify_on_flapping' => 1,
        'service_notify_on_downtime_scheduled' => 1,
        'service_notification_period' => 'none',
        'service_notification_command' => 'host-notify-by-email',
        'access' => 0,
        'password' => 'Contact!pwd1',
        'password2' => 'Contact!pwd1',
        'language' => 'en_US',
        'location' => 'America/Guadeloupe',
        'autologin_key' => 'contactAutologinKey',
        'authentication_source' => 'Centreon',
        'admin' => 1,
        'reach_API' => 1,
        'acl_groups' => 'ALL',
        'address1' => '1@localhost',
        'address2' => '2@localhost',
        'address3' => '3@localhost',
        'address4' => '4@localhost',
        'address5' => '5@localhost',
        'address6' => '6@localhost',
        'enabled' => 1,
        'comments' => 'contactComments'
    );

    private $updatedProperties = array(
        'name' => 'modifiedName',
        'alias' => 'modifiedAlias',
        'email' => 'modified@localhost',
        'pager' => 'modifiedContactPager',
        'contact_template' => 'contact_template',
        'contact_groups' => 'Guest',
        'notifications_enabled' => 0,
        'host_notify_on_none' => 0,
        'host_notify_on_down' => 1,
        'host_notify_on_unreachable' => 1,
        'host_notify_on_recovery' => 1,
        'host_notify_on_flapping' => 1,
        'host_notify_on_downtime_scheduled' => 1,
        'host_notification_period' => 'nonworkhours',
        'host_notification_command' => 'service-notify-by-epager',
        'service_notify_on_warning' => 0,
        'service_notify_on_unknown' => 0,
        'service_notify_on_critical' => 0,
        'service_notify_on_recovery' => 0,
        'service_notify_on_flapping' => 0,
        'service_notify_on_downtime_scheduled' => 0,
        'service_notify_on_none' => 1,
        'service_notification_period' => 'workhours',
        'service_notification_command' => 'service-notify-by-jabber',
        'access' => 1,
        'password' => '',
        'password2' => '',
        'language' => 'Detection by browser',
        'location' => 'Europe/Paris',
        'autologin_key' => 'modifiedContactAutologinKey',
        'authentication_source' => 'Centreon',
        'admin' => 0,
        'reach_API' => 0,
        'acl_groups' => '',
        'address1' => '7@localhost',
        'address2' => '8@localhost',
        'address3' => '9@localhost',
        'address4' => '10@localhost',
        'address5' => '11@localhost',
        'address6' => '12@localhost',
        'enabled' => 1,
        'comments' => 'modifiedContactComments'
    );

    /**
     * @Given a contact is configured
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
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ContactConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['alias']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($key != 'password' && $key != 'password2') {
                            if ($key != 'password' && $key != 'password2' && $value != $object[$key]) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I duplicate a contact
     */
    public function iDuplicateAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new contact has the same properties
     */
    public function theNewContactHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ContactConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['alias'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'password' && $key != 'password2') {
                            if ($key != 'name' && $key != 'alias' && $value != $object[$key]) {
                                if (is_array($value)) {
                                    $value = implode(' ', $value);
                                }
                                if ($value != $object[$key]) {
                                    $this->tableau[] = $key;
                                }
                            }
                            if (($key == 'name' || $key == 'alias') && $value . '_1' != $object[$key]) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I delete a contact
     */
    public function iDeleteAContact()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted contact is not displayed in the list
     */
    public function theDeletedContactIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The service is not being deleted.",
            5
        );
    }
}
