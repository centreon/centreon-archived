<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostGroupServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\HostGroupServiceConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;

class HostGroupServiceConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $serviceCategory1 = array(
        'name' => 'serviceCategory1Name',
        'description' => 'serviceCategory1Description',
        'severity' => 1,
        'level' => 3,
        'icon' => '       centreon (png)'
    );

    protected $serviceCategory2 = array(
        'name' => 'serviceCategory2Name',
        'description' => 'serviceCategory2Description',
        'severity' => 1,
        'level' => 2,
        'icon' => '       centreon (png)'
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
        'hosts' => 'Windows-Servers',
        'description' => 'serviceDescription',
        'templates' => 'generic-service',
        'check_command' => 'check_http',
        'macros' => array(
            'MACRONAME' => 22
        ),
        'check_period' => 'workhours',
        'max_check_attempts' => 15,
        'normal_check_interval' => 7,
        'retry_check_interval' => 9,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 1,
        'is_volatile' => 0,
        'notifications_enabled' => 2,
        'inherits_contacts_groups' => 0,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notification_interval' => 23,
        'notify_on_none' => 1,
        'notify_on_warning' => 0,
        'notify_on_unknown' => 0,
        'notify_on_critical' => 0,
        'notify_on_recovery' => 0,
        'notify_on_flapping' => 0,
        'notify_on_downtime_scheduled' => 0,
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'service_groups' => 'serviceGroup1Name',
        'trap_relations' => 'Generic - coldStart',
        'obsess_over_service' => 2,
        'acknowledgement_timeout' => 34,
        'check_freshness' => 1,
        'freshness_threshold' => 22,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 15,
        'high_flap_threshold' => 18,
        'retain_status_information' => 2,
        'retain_non_status_information' => 1,
        'stalking_on_ok' => 1,
        'stalking_on_warning' => 1,
        'stalking_on_unknown' => 0,
        'stalking_on_critical' => 0,
        'event_handler_enabled' => 0,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'eventHandlerArgument',
        'graph_template' => 'CPU',
        'service_categories' => 'Disk',
        'url' => 'serviceUrl',
        'notes' => 'serviceNotes',
        'action_url' => 'serviceActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'serviceAltIcon',
        'severity' => 'serviceCategory2Name (2)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 1,
        'comments' => 'serviceComments'
    );

    protected $duplicatedProperties = array(
        'hosts' => 'Windows-Servers',
        'description' => 'serviceDescription_1',
        'templates' => 'generic-service',
        'check_command' => 'check_http',
        'macros' => array(
            'MACRONAME' => 22
        ),
        'check_period' => 'workhours',
        'max_check_attempts' => 15,
        'normal_check_interval' => 7,
        'retry_check_interval' => 9,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 1,
        'is_volatile' => 0,
        'notifications_enabled' => 2,
        'inherits_contacts_groups' => 0,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notification_interval' => 23,
        'notify_on_none' => 1,
        'notify_on_warning' => 0,
        'notify_on_unknown' => 0,
        'notify_on_critical' => 0,
        'notify_on_recovery' => 0,
        'notify_on_flapping' => 0,
        'notify_on_downtime_scheduled' => 0,
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'service_groups' => 'serviceGroup1Name',
        'trap_relations' => 'Generic - coldStart',
        'obsess_over_service' => 2,
        'acknowledgement_timeout' => 34,
        'check_freshness' => 1,
        'freshness_threshold' => 22,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 15,
        'high_flap_threshold' => 18,
        'retain_status_information' => 2,
        'retain_non_status_information' => 1,
        'stalking_on_ok' => 1,
        'stalking_on_warning' => 1,
        'stalking_on_unknown' => 0,
        'stalking_on_critical' => 0,
        'event_handler_enabled' => 0,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'eventHandlerArgument',
        'graph_template' => 'CPU',
        'service_categories' => 'Disk',
        'url' => 'serviceUrl',
        'notes' => 'serviceNotes',
        'action_url' => 'serviceActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'serviceAltIcon',
        'severity' => 'serviceCategory2Name (2)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 1,
        'comments' => 'serviceComments'
    );

    protected $update = array(
        'hosts' => 'Firewall',
        'description' => 'serviceDescriptionChanged',
        'templates' => 'Ping-WAN',
        'check_command' => 'check_https',
        'macros' => array(
            'MACRONAMECHANGED' => 11
        ),
        'check_period' => 'none',
        'max_check_attempts' => 32,
        'normal_check_interval' => 81,
        'retry_check_interval' => 12,
        'active_checks_enabled' => 0,
        'passive_checks_enabled' => 2,
        'is_volatile' => 1,
        'notifications_enabled' => 0,
        'inherits_contacts_groups' => 1,
        'notification_interval' => 14,
        'notify_on_none' => 0,
        'notify_on_warning' => 1,
        'notify_on_unknown' => 1,
        'notify_on_critical' => 1,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 1,
        'first_notification_delay' => 8,
        'recovery_notification_delay' => 9,
        'service_groups' => 'serviceGroup2Name',
        'trap_relations' => 'HP - snTrapL4GslbRemoteControllerUp',
        'obsess_over_service' => 0,
        'acknowledgement_timeout' => 28,
        'check_freshness' => 2,
        'freshness_threshold' => 31,
        'flap_detection_enabled' => 1,
        'low_flap_threshold' => 42,
        'high_flap_threshold' => 79,
        'retain_status_information' => 1,
        'retain_non_status_information' => 0,
        'stalking_on_ok' => 0,
        'stalking_on_warning' => 0,
        'stalking_on_unknown' => 1,
        'stalking_on_critical' => 1,
        'event_handler_enabled' => 1,
        'event_handler' => 'check_http',
        'event_handler_arguments' => 'eventHandlerArgumentChanged',
        'graph_template' => 'Storage',
        'service_categories' => 'Memory',
        'url' => 'serviceUrlChanged',
        'notes' => 'serviceNotesChanged',
        'action_url' => 'serviceActionUrlChanged',
        'icon' => '',
        'alt_icon' => 'Empty',
        'severity' => 'serviceCategory1Name (3)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 1,
        'comments' => 'serviceCommentsChanged'
    );

    protected $updatedProperties = array(
        'hosts' => 'Firewall',
        'description' => 'serviceDescriptionChanged',
        'templates' => 'Ping-WAN',
        'check_command' => 'check_https',
        'macros' => array(
            'MACRONAMECHANGED' => 11,
            'MACRONAME' => 22
        ),
        'check_period' => 'none',
        'max_check_attempts' => 32,
        'normal_check_interval' => 81,
        'retry_check_interval' => 12,
        'active_checks_enabled' => 0,
        'passive_checks_enabled' => 2,
        'is_volatile' => 1,
        'notifications_enabled' => 0,
        'inherits_contacts_groups' => 1,
        'contacts' => '',
        'contact_groups' => '',
        'notification_interval' => 14,
        'notify_on_none' => 0,
        'notify_on_warning' => 1,
        'notify_on_unknown' => 1,
        'notify_on_critical' => 1,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 1,
        'first_notification_delay' => 8,
        'recovery_notification_delay' => 9,
        'service_groups' => 'serviceGroup2Name',
        'trap_relations' => array(
            'HP - snTrapL4GslbRemoteControllerUp'
        ),
        'obsess_over_service' => 0,
        'acknowledgement_timeout' => 28,
        'check_freshness' => 2,
        'freshness_threshold' => 31,
        'flap_detection_enabled' => 1,
        'low_flap_threshold' => 42,
        'high_flap_threshold' => 79,
        'retain_status_information' => 1,
        'retain_non_status_information' => 0,
        'stalking_on_ok' => 0,
        'stalking_on_warning' => 0,
        'stalking_on_unknown' => 1,
        'stalking_on_critical' => 1,
        'event_handler_enabled' => 1,
        'event_handler' => 'check_http',
        'event_handler_arguments' => 'eventHandlerArgumentChanged',
        'graph_template' => 'Storage',
        'service_categories' => 'Memory',
        'url' => 'serviceUrlChanged',
        'notes' => 'serviceNotesChanged',
        'action_url' => 'serviceActionUrlChanged',
        'icon' => '',
        'alt_icon' => 'Empty',
        'severity' => 'serviceCategory1Name (3)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 1,
        'comments' => 'serviceCommentsChanged'
    );

    /**
     * @Given a service is configured
     */
    public function aServiceIsConfigured()
    {
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory1);
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory2);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup1);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup2);
        $this->currentPage->save();
        $this->currentPage = new HostGroupServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host group service
     */
    public function iChangeThePropertiesOfAHostGroupService()
    {
        $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['description']);
        $this->currentPage->setProperties($this->update);
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
                    $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['description']);
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
     * @When I duplicate a host group service
     */
    public function iDuplicateAHostGroupService()
    {
        $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry(array(
            'service' => $this->initialProperties['description'],
            'host_group' => $this->initialProperties['hosts']
        ));
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new host group service has the same properties
     */
    public function theNewHostGroupServiceHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['description']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
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
     * @When I delete a host group service
     */
    public function iDeleteAHostGroupService()
    {
        $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry(array(
            'service' => $this->initialProperties['description'],
            'host_group' => $this->initialProperties['hosts']
        ));
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted host group service is not displayed in the list
     */
    public function theDeletedHostGroupServiceIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new HostGroupServiceConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $host => $service) {
                    foreach ($service as $value) {
                        $bool = $bool && $value['service'] != $this->initialProperties['description'];
                    }
                }
                return $bool;
            },
            "The service is not being deleted.",
            5
        );
    }
}
