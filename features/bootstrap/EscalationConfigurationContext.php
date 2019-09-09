<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\EscalationConfigurationPage;
use Centreon\Test\Behat\Configuration\EscalationConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;

class EscalationConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $host = array(
        'name' => 'hostName',
        'alias' => 'hostAlias',
        'address' => 'host@localhost'
    );

    protected $metaService1 = array(
        'name' => 'metaService1Name',
        'max_check_attempts' => 3
    );

    protected $metaService2 = array(
        'name' => 'metaService2Name',
        'max_check_attempts' => 4
    );

    protected $serviceGroup1 = array(
        'name' => 'serviceGroup1Name',
        'description' => 'serviceGroup1Description'
    );

    protected $serviceGroup2 = array(
        'name' => 'serviceGroup2Name',
        'description' => 'serviceGroup2Description'
    );

    protected $initialProperties = array(
        'name' => 'escalationName',
        'alias' => 'escalationAlias',
        'first_notification' => 5,
        'last_notification' => 15,
        'notification_interval' => 8,
        'escalation_period' => '24x7',
        'host_notify_on_down' => 1,
        'host_notify_on_unreachable' => 0,
        'host_notify_on_recovery' => 1,
        'service_notify_on_warning' => 0,
        'service_notify_on_unknown' => 1,
        'service_notify_on_critical' => 0,
        'service_notify_on_recovery' => 1,
        'contactgroups' => 'Supervisors',
        'comment' => 'escalationComment',
        'host_inheritance_to_services' => 1,
        'hosts' => 'Centreon-Server',
        'services' => 'Centreon-Server - Load',
        'hostgroup_inheritance_to_services' => 0,
        'hostgroups' => 'Linux-Servers',
        'servicegroups' => 'serviceGroup1Name',
        'metaservices' => 'metaService1Name'
    );

    protected $updatedProperties = array(
        'name' => 'escalationNameChanged',
        'alias' => 'escalationAliasChanged',
        'first_notification' => 12,
        'last_notification' => 27,
        'notification_interval' => 14,
        'escalation_period' => 'workhours',
        'host_notify_on_down' => 0,
        'host_notify_on_unreachable' => 1,
        'host_notify_on_recovery' => 0,
        'service_notify_on_warning' => 1,
        'service_notify_on_unknown' => 0,
        'service_notify_on_critical' => 1,
        'service_notify_on_recovery' => 0,
        'contactgroups' => 'Guest',
        'comment' => 'escalationCommentChanged',
        'host_inheritance_to_services' => 0,
        'hosts' => 'hostName',
        'services' => 'Centreon-Server - Memory',
        'hostgroup_inheritance_to_services' => 1,
        'hostgroups' => 'Networks',
        'servicegroups' => 'serviceGroup2Name',
        'metaservices' => 'metaService2Name'
    );

    /**
     * @Given an escalation is configured
     */
    public function anEscalationIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService1);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService2);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup1);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup2);
        $this->currentPage->save();
        $this->currentPage = new EscalationConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of an escalation
     */
    public function iChangeThePropertiesOfAnEscalation()
    {
        $this->currentPage = new EscalationConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
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
                    $this->currentPage = new EscalationConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
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
     * @When I duplicate an escalation
     */
    public function iDuplicateAnEscalation()
    {
        $this->currentPage = new EscalationConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new escalation has the same properties
     */
    public function theNewEscalationHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new EscalationConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['name'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'name' && $value != $object[$key]) {
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
     * @When I delete an escalation
     */
    public function iDeleteAnEscalation()
    {
        $this->currentPage = new EscalationConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted escalation is not displayed in the list
     */
    public function theDeletedEscalationIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new EscalationConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['service'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The service is not being deleted.",
            5
        );
    }
}
