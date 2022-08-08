<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MassiveChangeHostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\HostCategoryConfigurationPage;

class MassiveChangeHostsContext extends CentreonContext
{
    protected $currentPage;

    protected $host1 = array(
        'name' => 'host1Name',
        'alias' => 'host1Alias',
        'address' => 'host1@localhost'
    );

    protected $host2 = array(
        'name' => 'host2Name',
        'alias' => 'host2Alias',
        'address' => 'host2@localhost'
    );

    protected $host3 = array(
        'name' => 'host3Name',
        'alias' => 'host3Alias',
        'address' => 'host3@localhost'
    );

    protected $hostGroup = array(
        'name' => 'hostGroupName',
        'alias' => 'hostGroupAlias'
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

    protected $updatedProperties = array(
        'snmp_community' => 'snmp',
        'snmp_version' => '2c',
        'monitored_from' => 'Central',
        'monitored_option' => 1,
        'location' => 'Europe/Paris',
        'update_mode_tplp' => 0,
        'templates' => array(
            'generic-host'
        ),
        'service_linked_to_template' => 0,
        'command_arguments' => 'hostCommandArgument',
        'macros' => array(
            'HOSTMACRONAME' => '22'
        ),
        'check_command' => 'check_http',
        'check_period' => 'workhours',
        'max_check_attempts' => 34,
        'normal_check_interval' => 5,
        'retry_check_interval' => 10,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 0,
        'notifications_enabled' => 1,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'update_mode_notifopts' => 1,
        'notify_on_down' => 1,
        'notify_on_unreachable' => 1,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 1,
        'notify_on_none' => 0,
        'notification_interval' => 17,
        'update_mode_notif_interval' => 0,
        'update_mode_timeperiod' => 0,
        'notification_period' => 'none',
        'update_mode_first_notif_delay' => 1,
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'update_mode_hhg' => 0,
        'parent_host_groups' => 'hostGroupName',
        'update_mode_hhc' => 1,
        'parent_host_categories' => 'hostCategoryName2',
        'update_mode_hpar' => 1,
        'parent_hosts' => 'Centreon-Server',
        'update_mode_hch' => 0,
        'child_hosts' => 'host3Name',
        'obsess_over_host' => 2,
        'acknowledgement_timeout' => 2,
        'check_freshness' => 0,
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
        'status_map_image' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '15,84',
        '3d_coords' => '15,84,76',
        'severity_level' => 'hostCategoryName1 (2)',
        'enabled' => 0,
        'comments' => 'hostMassiveChangeComments'
    );

    protected $updatedHost1 = array(
        'name' => 'host1Name',
        'alias' => 'host1Alias',
        'address' => 'host1@localhost',
        'snmp_community' => 'snmp',
        'snmp_version' => '2c',
        'monitored_from' => 'Central',
        'location' => 'Europe/Paris',
        'templates' => array(
            'generic-host'
        ),
        'service_linked_to_template' => 0,
        'check_command' => 'check_http',
        'command_arguments' => 'hostCommandArgument',
        'macros' => array(
            'HOSTMACRONAME' => '22'
        ),
        'check_period' => 'workhours',
        'max_check_attempts' => 34,
        'normal_check_interval' => 5,
        'retry_check_interval' => 10,
        'active_checks_enabled' => 2,
        'passive_checks_enabled' => 0,
        'notifications_enabled' => 1,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notify_on_down' => 1,
        'notify_on_unreachable' => 1,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 1,
        'notify_on_none' => 0,
        'notification_interval' => 17,
        'notification_period' => 'none',
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'parent_host_groups' => 'hostGroupName',
        'parent_host_categories' => 'hostCategoryName2',
        'parent_hosts' => 'Centreon-Server',
        'child_hosts' => 'host3Name',
        'obsess_over_host' => 2,
        'acknowledgement_timeout' => 2,
        'check_freshness' => 0,
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
        'status_map_image' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '15,84',
        '3d_coords' => '15,84,76',
        'severity_level' => 'hostCategoryName1 (2)',
        'enabled' => 0,
        'comments' => 'hostMassiveChangeComments'
    );

    protected $updatedHost2 = array(
        'name' => 'host2Name',
        'alias' => 'host2Alias',
        'address' => 'host2@localhost',
        'snmp_community' => 'snmp',
        'snmp_version' => '2c',
        'monitored_from' => 'Central',
        'location' => 'Europe/Paris',
        'templates' => array(
            'generic-host'
        ),
        'service_linked_to_template' => 0,
        'check_command' => 'check_http',
        'command_arguments' => 'hostCommandArgument',
        'macros' => array(
            'HOSTMACRONAME' => '22'
        ),
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
        'notify_on_unreachable' => 1,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 1,
        'notification_interval' => 17,
        'notification_period' => 'none',
        'first_notification_delay' => 4,
        'recovery_notification_delay' => 3,
        'parent_host_groups' => 'hostGroupName',
        'parent_host_categories' => 'hostCategoryName2',
        'parent_hosts' => 'Centreon-Server',
        'child_hosts' => 'host3Name',
        'obsess_over_host' => 2,
        'acknowledgement_timeout' => 2,
        'check_freshness' => 0,
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
        'status_map_image' => '       centreon (png)',
        'geo_coordinates' => '2.3522219,48.856614',
        '2d_coords' => '15,84',
        '3d_coords' => '15,84,76',
        'severity_level' => 'hostCategoryName1 (2)',
        'enabled' => 0,
        'comments' => 'hostMassiveChangeComments'
    );

    /**
     * @Given several hosts have been created with mandatory properties
     */
    public function severalHostsHaveBeenCreatedWithMandatoryProperties()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host3);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory1);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory2);
        $this->currentPage->save();
    }

    /**
     * @When I have applied Massive Change operation to several hosts
     */
    public function iHaveAppliedMassiveChangeOperationToSeveralHosts()
    {
        $this->currentPage = new HostConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->host1['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $object = $this->currentPage->getEntry($this->host2['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->selectInList('select[name="o1"]', 'Massive Change');



        $this->currentPage = new MassiveChangeHostConfigurationPage($this, false);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then all selected hosts are updated with the same values
     */
    public function allSelectedHostsAreUpdatedWithTheSameValues()
    {
        foreach (array($this->updatedHost1, $this->updatedHost2) as $hostProperties) {
            $this->notUpdatedProperties = array();

            $this->currentPage = new HostConfigurationListingPage($this);
            $this->currentPage = $this->currentPage->inspect($hostProperties['name']);

            try {
                $this->spin(
                    function ($context) use ($hostProperties) {
                        $object = $context->currentPage->getProperties();
                        foreach ($hostProperties as $key => $value) {
                            if ($value != $object[$key]) {
                                $context->notUpdatedProperties[] = $key;
                            }
                        }
                        return count($context->notUpdatedProperties) == 0;
                    },
                    'Some properties have not been updated',
                    5
                );
            } catch (\Exception $e) {
                throw new \Exception(
                    "Some properties have not been update on host " . $hostProperties['name'] . " : " .
                    implode(',', array_unique($this->notUpdatedProperties))
                );
            }
        }
    }
}
