<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\HostCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\HostTemplateConfigurationPage;

class HostConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $host2 = array(
        'name' => 'hostName2',
        'alias' => 'hostAlias2',
        'address' => 'host2@localhost'
    );

    protected $host3 = array(
        'name' => 'hostName3',
        'alias' => 'hostAlias3',
        'address' => 'host3@localhost'
    );

    protected $hostGroup1 = array(
        'name' => 'hostGroupName1',
        'alias' => 'hostGroupAlias1'
    );

    protected $hostGroup2 = array(
        'name' => 'hostGroupName2',
        'alias' => 'hostGroupAlias2'
    );

    protected $hostCategory1 = array(
        'name' => 'hostCategoryName1',
        'alias' => 'hostCategoryAlias1',
        'severity' => 1,
        'severity_level' => 2,
        'severity_icon' => '       centreon (png)'
    );

    protected $hostCategory2 = array(
        'name' => 'hostCategoryName2',
        'alias' => 'hostCategoryAlias2'
    );

    protected $hostCategory3 = array(
        'name' => 'hostCategoryName3',
        'alias' => 'hostCategoryAlias3'
    );

    protected $hostTemplate = array(
        'name' => 'hostTemplateName',
        'alias' => 'hostTemplateAlias'
    );

    protected $initialProperties = array(
        'name' => 'hostName',
        'alias' => 'hostAlias',
        'address' => 'host@localhost',
        'snmp_community' => 'hostSnmpCommunity',
        'snmp_version' => '1',
        'location' => 'America/Caracas',
        'templates' => array(
            'generic-host'
        ),
        'check_command' => 'check_http',
        'command_arguments' => 'hostCommandArgument',
        'check_period' => 'workhours',
        'max_check_attempts' => 34,
        'normal_check_interval' => 5,
        'retry_check_interval' => 10,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 0,
        'notifications_enabled' => 1,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notify_on_none' => 0,
        'notify_on_down' => 1,
        'notify_on_unreachable' => 0,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 0,
        'notify_on_downtime_scheduled' => 1,
        'notification_interval' => 17,
        'notification_period' => 'none',
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'parent_host_groups' => 'hostGroupName1',
        'parent_host_categories' => 'hostCategoryName2',
        'parent_hosts' => 'Centreon-Server',
        'child_hosts' => 'hostName2',
        'obsess_over_host' => 2,
        'acknowledgement_timeout' => 2,
        'check_freshness' => 1,
        'freshness_threshold' => 34,
        'flap_detection_enabled' => 1,
        'low_flap_threshold' => 67,
        'high_flap_threshold' => 85,
        'retain_status_information' => 2,
        'retain_non_status_information' => 0,
        'stalking_option_on_up' => 1,
        'stalking_option_on_down' => 0,
        'stalking_option_on_unreachable' => 1,
        'event_handler_enabled' => 2,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'event_handler_arguments',
        'url' => 'hostMassiveChangeUrl',
        'notes' => 'hostMassiveChangeNotes',
        'action_url' => 'hostMassiveChangeActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'hostMassiveChangeIcon',
        'status_map_image' => '',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '15,84',
        '3d_coords' => '15,84,76',
        'severity_level' => 'hostCategoryName1 (2)',
        'enabled' => 1,
        'comments' => 'hostMassiveChangeComments'
    );

    protected $duplicatedProperties = array(
        'name' => 'hostName_1',
        'alias' => 'hostAlias',
        'address' => 'host@localhost',
        'snmp_community' => 'hostSnmpCommunity',
        'snmp_version' => '1',
        'location' => 'America/Caracas',
        'templates' => array(
            'generic-host'
        ),
        'check_command' => 'check_http',
        'command_arguments' => 'hostCommandArgument',
        'check_period' => 'workhours',
        'max_check_attempts' => 34,
        'normal_check_interval' => 5,
        'retry_check_interval' => 10,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 0,
        'notifications_enabled' => 1,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notify_on_none' => 0,
        'notify_on_down' => 1,
        'notify_on_unreachable' => 0,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 0,
        'notify_on_downtime_scheduled' => 1,
        'notification_interval' => 17,
        'notification_period' => 'none',
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'parent_host_groups' => 'hostGroupName1',
        'parent_host_categories' => 'hostCategoryName2',
        'parent_hosts' => 'Centreon-Server',
        'child_hosts' => 'hostName2',
        'obsess_over_host' => 2,
        'acknowledgement_timeout' => 2,
        'check_freshness' => 1,
        'freshness_threshold' => 34,
        'flap_detection_enabled' => 1,
        'low_flap_threshold' => 67,
        'high_flap_threshold' => 85,
        'retain_status_information' => 2,
        'retain_non_status_information' => 0,
        'stalking_option_on_up' => 1,
        'stalking_option_on_down' => 0,
        'stalking_option_on_unreachable' => 1,
        'event_handler_enabled' => 2,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'event_handler_arguments',
        'url' => 'hostMassiveChangeUrl',
        'notes' => 'hostMassiveChangeNotes',
        'action_url' => 'hostMassiveChangeActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'hostMassiveChangeIcon',
        'status_map_image' => '',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '15,84',
        '3d_coords' => '15,84,76',
        'severity_level' => 'hostCategoryName1 (2)',
        'enabled' => 1,
        'comments' => 'hostMassiveChangeComments'
    );

    protected $updatedProperties = array(
        'name' => 'hostNameChanged',
        'alias' => 'hostAliasChanged',
        'address' => 'hostChanged@localhost',
        'snmp_community' => 'hostSnmpCommunitychanged',
        'snmp_version' => '3',
        'macros' => array(
            'HOSTMACROCHANGED' => 5
        ),
        'location' => 'Europe/Paris',
        'templates' => array(
            'hostTemplateName'
        ),
        'check_command' => 'check_https',
        'command_arguments' => 'hostCommandArgumentChanged',
        'check_period' => 'none',
        'max_check_attempts' => 43,
        'normal_check_interval' => 4,
        'retry_check_interval' => 25,
        'active_checks_enabled' => 0,
        'passive_checks_enabled' => 1,
        'notifications_enabled' => 0,
        'contacts' => 'User',
        'contact_groups' => 'Guest',
        'notify_on_none' => 0,
        'notify_on_down' => 0,
        'notify_on_unreachable' => 1,
        'notify_on_recovery' => 0,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 0,
        'notification_interval' => 34,
        'notification_period' => 'workhours',
        'first_notification_delay' => 7,
        'recovery_notification_delay' => 4,
        'parent_host_groups' => 'hostGroupName2',
        'parent_host_categories' => 'hostCategoryName3',
        'parent_hosts' => 'hostName3',
        'child_hosts' => 'Centreon-Server',
        'obsess_over_host' => 1,
        'acknowledgement_timeout' => 0,
        'check_freshness' => 2,
        'freshness_threshold' => 65,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 38,
        'high_flap_threshold' => 51,
        'retain_status_information' => 1,
        'retain_non_status_information' => 1,
        'stalking_option_on_up' => 0,
        'stalking_option_on_down' => 1,
        'stalking_option_on_unreachable' => 0,
        'event_handler_enabled' => 1,
        'event_handler' => 'check_http',
        'event_handler_arguments' => 'eventHandlerArgumentsChanged',
        'url' => 'hostMassiveChangeUrlChanged',
        'notes' => 'hostMassiveChangeNotesChanged',
        'action_url' => 'hostMassiveChangeActionUrlChanged',
        'icon' => '',
        'alt_icon' => 'hostMassiveChangeIconChanged',
        'status_map_image' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '2,3',
        '3d_coords' => '42,24,66',
        'severity_level' => '',
        'enabled' => 1,
        'comments' => 'hostMassiveChangeCommentsChanged'
    );

    /**
     * @Given an host is configured
     */
    public function anHostIsConfigured()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host3);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup1);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup2);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory1);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory2);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory3);
        $this->currentPage->save();
        $this->currentPage = new HostTemplateConfigurationPage($this);
        $this->currentPage->setProperties($this->hostTemplate);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a host
     */
    public function iChangeThePropertiesOfAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then its properties are updated
     */
    public function itsPropertiesAreUpdated()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
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
     * @When I duplicate a host
     */
    public function iDuplicateAHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new host is created with identical properties
     */
    public function aNewHostIsCreatedWithIdenticalProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new HostConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($value != $object[$key]) {
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
     * @When I delete the host
     */
    public function iDeleteTheHost()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the host is not visible anymore
     */
    public function theHostIsNotVisibleAnymore()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new HostConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The host is not being deleted.",
            5
        );
    }
}
