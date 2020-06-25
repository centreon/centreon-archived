<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MassiveChangeServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceTemplateConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\SnmpTrapsConfigurationPage;

class MassiveChangeServicesContext extends CentreonContext
{
    protected $currentPage;

    protected $contact = array(
        'alias' => 'contactAlias',
        'name' => 'contactName',
        'email' => 'contact@localhost'
    );

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

    protected $service1 = array(
        'description' => 'service1Description',
        'hosts' => 'host1Name',
        'check_command' => 'check_centreon_cpu'
    );

    protected $service2 = array(
        'description' => 'service2Description',
        'hosts' => 'host2Name',
        'check_command' => 'check_https'
    );

    protected $serviceCategory = array(
        'name' => 'serviceCategoryName',
        'description' => 'serviceCategoryDescription',
        'severity' => 1,
        'level' => 12,
        'icon' => '       centreon (png)'
    );

    protected $serviceGroup = array(
        'name' => 'serviceGroupName',
        'description' => 'serviceGroupDescription'
    );

    protected $serviceTemplate = array(
        'description' => 'hostTemplateDescription',
        'alias' => 'hostTemplateAlias'
    );

    protected $trapSNMP = array(
        'name' => 'trapName',
        'oid' => '.1.2.3.4.56',
        'vendor' => 'Generic',
        'output' => 'trapOutputMessage'
    );

    protected $updatedProperties = array(
        'update_mode_pars' => 1,
        'hosts' => 'host3Name',
        'templates' => 'hostTemplateDescription',
        'check_command' => 'check_local_disk',
        'macros' => array(
            'SERVICEMACRO' => 22
        ),
        'check_period' => '24x7',
        'max_check_attempts' => 10,
        'normal_check_interval' => 49,
        'retry_check_interval' => 32,
        'active_checks_enabled' => 1,
        'passive_checks_enabled' => 0,
        'is_volatile' => 0,
        'notifications_enabled' => 2,
        'inherits_contacts_groups' => 0,
        'update_mode_cgs' => 1,
        'contacts' => 'contactName',
        'contact_groups' => 'Supervisors',
        'update_mode_notif_interval' => 1,
        'notification_interval' => 15,
        'update_mode_notif_timeperiod' => 0,
        'notification_period' => 'workhours',
        'update_mode_notif_options' => 1,
        'notify_on_warning' => 0,
        'notify_on_unknown' => 1,
        'notify_on_critical' => 0,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 0,
        'update_mode_first_notif_delay' => 1,
        'first_notification_delay' => 29,
        'recovery_notification_delay' => 36,
        'update_mode_sgs' => 0,
        'service_groups' => 'serviceGroupName',
        'update_mode_traps' => 1,
        'trap_relations' => 'Generic - trapName',
        'obsess_over_service' => 2,
        'acknowledgement_timeout' => 7,
        'check_freshness' => 1,
        'freshness_threshold' => 5,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 37,
        'high_flap_threshold' => 89,
        'retain_status_information' => 2,
        'retain_non_status_information' => 0,
        'stalking_on_ok' => 0,
        'stalking_on_warning' => 1,
        'stalking_on_unknown' => 1,
        'stalking_on_critical' => 0,
        'event_handler_enabled' => 2,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'serviceEventHandlerArgument',
        'graph_template' => 'Storage',
        'update_mode_sc' => 1,
        'service_categories' => 'Memory',
        'url' => 'serviceUrl',
        'notes' => 'serviceNotes',
        'action_url' => 'serviceActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'serviceIcon',
        'severity' => 'serviceCategoryName (12)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 0,
        'comments' => 'serviceComments'
    );

    protected $updatedService1 = array(
        'description' => 'service1Description',
        'hosts' => 'host3Name',
        'check_command' => 'check_centreon_cpu',
        'templates' => 'hostTemplateDescription',
        'check_command' => 'check_local_disk',
        'macros' => array(
            'SERVICEMACRO' => 22
        ),
        'check_period' => '24x7',
        'max_check_attempts' => 10,
        'normal_check_interval' => 49,
        'retry_check_interval' => 32,
        'active_checks_enabled' => 1,
        'passive_checks_enabled' => 0,
        'is_volatile' => 0,
        'notifications_enabled' => 2,
        'inherits_contacts_groups' => 0,
        'contacts' => 'contactName',
        'contact_groups' => 'Supervisors',
        'notification_interval' => 15,
        'notification_period' => 'workhours',
        'notify_on_warning' => 0,
        'notify_on_unknown' => 1,
        'notify_on_critical' => 0,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 0,
        'notify_on_none' => 0,
        'first_notification_delay' => 29,
        'recovery_notification_delay' => 36,
        'service_groups' => 'serviceGroupName',
        'trap_relations' => 'Generic - trapName',
        'obsess_over_service' => 2,
        'acknowledgement_timeout' => 7,
        'check_freshness' => 1,
        'freshness_threshold' => 5,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 37,
        'high_flap_threshold' => 89,
        'retain_status_information' => 2,
        'retain_non_status_information' => 0,
        'stalking_on_ok' => 0,
        'stalking_on_warning' => 1,
        'stalking_on_unknown' => 1,
        'stalking_on_critical' => 0,
        'event_handler_enabled' => 2,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'serviceEventHandlerArgument',
        'graph_template' => 'Storage',
        'service_categories' => 'Memory',
        'url' => 'serviceUrl',
        'notes' => 'serviceNotes',
        'action_url' => 'serviceActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'serviceIcon',
        'severity' => 'serviceCategoryName (12)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 0,
        'comments' => 'serviceComments'
    );

    protected $updatedService2 = array(
        'description' => 'service2Description',
        'hosts' => 'host3Name',
        'check_command' => 'check_https',
        'templates' => 'hostTemplateDescription',
        'check_command' => 'check_local_disk',
        'macros' => array(
            'SERVICEMACRO' => 22
        ),
        'check_period' => '24x7',
        'max_check_attempts' => 10,
        'normal_check_interval' => 49,
        'retry_check_interval' => 32,
        'active_checks_enabled' => 1,
        'passive_checks_enabled' => 0,
        'is_volatile' => 0,
        'notifications_enabled' => 2,
        'inherits_contacts_groups' => 0,
        'contacts' => 'contactName',
        'contact_groups' => 'Supervisors',
        'notification_interval' => 15,
        'notification_period' => 'workhours',
        'notify_on_warning' => 0,
        'notify_on_unknown' => 1,
        'notify_on_critical' => 0,
        'notify_on_recovery' => 1,
        'notify_on_flapping' => 1,
        'notify_on_downtime_scheduled' => 0,
        'notify_on_none' => 0,
        'first_notification_delay' => 29,
        'recovery_notification_delay' => 36,
        'service_groups' => 'serviceGroupName',
        'trap_relations' => 'Generic - trapName',
        'obsess_over_service' => 2,
        'acknowledgement_timeout' => 7,
        'check_freshness' => 1,
        'freshness_threshold' => 5,
        'flap_detection_enabled' => 0,
        'low_flap_threshold' => 37,
        'high_flap_threshold' => 89,
        'retain_status_information' => 2,
        'retain_non_status_information' => 0,
        'stalking_on_ok' => 0,
        'stalking_on_warning' => 1,
        'stalking_on_unknown' => 1,
        'stalking_on_critical' => 0,
        'event_handler_enabled' => 2,
        'event_handler' => 'check_https',
        'event_handler_arguments' => 'serviceEventHandlerArgument',
        'graph_template' => 'Storage',
        'service_categories' => 'Memory',
        'url' => 'serviceUrl',
        'notes' => 'serviceNotes',
        'action_url' => 'serviceActionUrl',
        'icon' => '       centreon (png)',
        'alt_icon' => 'serviceIcon',
        'severity' => 'serviceCategoryName (12)',
        'geo_coordinates' => '2.3522219,48.856614',
        'status' => 0,
        'comments' => 'serviceComments'
    );

    /**
     * @Given several services have been created with mandatory properties
     */
    public function severalServicesHaveBeenCreatedWithMandatoryProperties()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->contact);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host3);
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->service1);
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->service2);
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup);
        $this->currentPage->save();
        $this->currentPage = new ServiceTemplateConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceTemplate);
        $this->currentPage->save();
        $this->currentPage = new SnmpTrapsConfigurationPage($this);
        $this->currentPage->setProperties($this->trapSNMP);
        $this->currentPage->save();
    }

    /**
     * @When I have applied Massive Change operation to several services
     */
    public function iHaveAppliedMassiveChangeOperationToSeveralServices()
    {
        $this->currentPage = new ServiceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry(array(
            'service' => $this->service1['description'],
            'host' => $this->service1['hosts']
        ));
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $object = $this->currentPage->getEntry(array(
            'service' => $this->service2['description'],
            'host' => $this->service2['hosts']
        ));
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->selectInList('select[name="o1"]', 'Massive Change');
        $this->currentPage = new MassiveChangeServiceConfigurationPage($this, false);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then all selected services are updated with the same values
     */
    public function allSelectedServicesAreUpdatedWithTheSameValues()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ServiceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedService1['description']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedService1 as $key => $value) {
                        if ($value != $object[$key]) {
                            $this->tableau[] = $key . ' 1';
                        }
                    }
                    $this->currentPage = new ServiceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedService2['description']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedService2 as $key => $value) {
                        if ($value != $object[$key]) {
                            $this->tableau[] = $key . ' 2';
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
}
