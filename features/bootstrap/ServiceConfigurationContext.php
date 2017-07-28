<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationListingPage;

class ServiceConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $contact1;

    protected $contact2;

    protected $contactGroup1;

    protected $contactGroup2;

    protected $host1;

    protected $host2;

    protected $serviceCategory1;

    protected $serviceCategory2;

    protected $serviceGroup1;

    protected $serviceGroup2;

    protected $serviceTemplate1;

    protected $serviceTemplate2;

    protected $initialProperties = array(
        // General tab
        'hosts' => 'host1Name',
        'descritpion' => 'serviceDescription',
        'templates' => 'serviceTemplate1Name',
        'check_command' => 'check_http',
        'macros' => array(
            'MACRONAME' => 22
        ),
        'check_period' => 'workhours',
        'max_check_attempts' => 15,
        'normal_check_interval' => 7,
        'retry_check_interval' => 9,
        'active_check_interval' => 2,
        'passive_check_interval' => 1,
        'is_volatile' => 0,
        // Notifications tab
        'notifications_enabled' => 2,
        'inherits_contact_groups' => 0,
        'contacts' => 'contact2Name',
        'contact_additive_inheritance' => 1,
        'contact_groups' => 'contactGroup1Name',
        'contact_group_additive_inheritance' => 0,
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
        // Relations tab
        'service_groups' => 'serviceGroup1Name',
        'trap_relations' => array(
            'Generic' => 'Generic - trapName'
        ),
        // Data tab
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
        // Extended graph
        'graph_template' => 'CPU',
        'service_categories' 
    );

    /**
     * @Given a service is configured
     */
    public function aServiceIsConfigured()
    {
    }

    /**
     * @When I change the properties of a service
     */
    public function iChangeThePropertiesOfAService()
    {
    }

    /**
     * @Then the properties are updated
     */
    public function thePropertiesAreUpdated()
    {
    }

    /**
     * @When I duplicate a service
     */
    public function iDuplicateAService()
    {
    }

    /**
     * @Then the new service has the same properties
     */
    public function theNewServiceHasTheSameProperties()
    {
    }

    /**
     * @When I delete a service
     */
    public function iDeleteAService()
    {
    }

    /**
     * @Then the deleted service is not displayed in the service list
     */
    public function theDeletedServiceIsNotDisplayedInTheServiceList()
    {
    }
}
