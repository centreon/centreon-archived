<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactTemplateConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactTemplateConfigurationListingPage;

class ContactTemplateConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'alias' => 'contactTemplateAlias',
        'name' => 'contactTemplateName',
        'contact_template' => 'contact_template',
        'notifications_enabled' => 1,
        'host_notification_on_down' => 1,
        'host_notification_on_unreachable' => 1,
        'host_notification_on_recovery' => 1,
        'host_notification_on_flapping' => 1,
        'host_notification_on_downtime_scheduled' => 1,
        'host_notification_on_none' => 0,
        'host_notification_period' => '24x7',
        'host_notification_command' => 'service-notify-by-email',
        'service_notification_on_none' => 1,
        'service_notification_on_warning' => 0,
        'service_notification_on_unknown' => 0,
        'service_notification_on_critical' => 0,
        'service_notification_on_recovery' => 0,
        'service_notification_on_flapping' => 0,
        'service_notification_on_downtime_scheduled' => 0,
        'service_notification_period' => 'none',
        'service_notification_command' => 'host-notify-by-email',
        'address1' => '1@localhost',
        'address2' => '2@localhost',
        'address3' => '3@localhost',
        'address4' => '4@localhost',
        'address5' => '5@localhost',
        'address6' => '6@localhost',
        'enabled' => 1,
        'comments' => 'contactTemplateComments'
    );

    protected $updatedProperties = array(
        'alias' => 'contactTemplateAliasChanged',
        'name' => 'contactTemplateNameChanged',
        'contact_template' => 'contact_template',
        'notifications_enabled' => 0,
        'host_notification_on_none' => 1,
        'host_notification_on_down' => 0,
        'host_notification_on_unreachable' => 0,
        'host_notification_on_recovery' => 0,
        'host_notification_on_flapping' => 0,
        'host_notification_on_downtime_scheduled' => 0,
        'host_notification_period' => 'nonworkhours',
        'host_notification_command' => 'service-notify-by-epager',
        'service_notification_on_warning' => 1,
        'service_notification_on_unknown' => 1,
        'service_notification_on_critical' => 1,
        'service_notification_on_recovery' => 1,
        'service_notification_on_flapping' => 1,
        'service_notification_on_none' => 0,
        'service_notification_on_downtime_scheduled' => 1,
        'service_notification_period' => 'workhours',
        'service_notification_command' => 'service-notify-by-jabber',
        'address1' => '7@localhost',
        'address2' => '8@localhost',
        'address3' => '9@localhost',
        'address4' => '10@localhost',
        'address5' => '11@localhost',
        'address6' => '12@localhost',
        'enabled' => 1,
        'comments' => 'contactTemplateCommentsChanged'
    );

    /**
     * @Given a service template is configured
     */
    public function aServiceTemplateIsConfigured()
    {
        $this->currentPage = new ContactTemplateConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a service template
     */
    public function iChangeThePropertiesOfAServiceTemplate()
    {
        $this->currentPage = new ContactTemplateConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['alias']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the properties are updated
     */
    public function thePropertiesAreUpdated()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ContactTemplateConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['alias']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if ($value != $object[$key]) {
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
     * @When I duplicate a service template
     */
    public function iDuplicateAServiceTemplate()
    {
        $this->currentPage = new ContactTemplateConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new service template has the same properties
     */
    public function theNewServiceTemplateHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ContactTemplateConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['alias'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
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
     * @When I delete a service template
     */
    public function iDeleteAServiceTemplate()
    {
        $this->currentPage = new ContactTemplateConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted service template is not displayed in the list
     */
    public function theDeletedServiceTemplateIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ContactTemplateConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['alias'] != $this->initialProperties['alias'];
                }
                return $bool;
            },
            "The service is not being deleted.",
            5
        );
    }
}
