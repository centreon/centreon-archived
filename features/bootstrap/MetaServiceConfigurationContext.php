<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationListingPage;

class MetaServiceConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'metaServiceName',
        'output_format' => 'metaServiceOutputFormat',
        'warning_level' => 75,
        'critical_level' => 90,
        'calculation_type' => 'Sum',
        'data_source_type' => 'ABSOLUTE',
        'selection_mode' => 2,
        'sql_like_clause_expression' => 'metaServiceExpression',
        'metric' => '',
        'check_period' => 'workhours',
        'max_check_attempts' => 10,
        'normal_check_interval' => 15,
        'retry_check_interval' => 5,
        'notification_enabled' => 1,
        'contacts' => 'User',
        'contact_groups' => 'Guest',
        'notification_interval' => 34,
        'notification_period' => 'none',
        'notification_on_warning' => 1,
        'notification_on_unknown' => 0,
        'notification_on_critical' => 1,
        'notification_on_recovery' => 0,
        'notification_on_flapping' => 1,
        'geo_coordinates' => '2.3522219,48.856614',
        'graph_template' => 'Latency',
        'enabled' => 1,
        'comments' => 'metaServiceComments'
    );

    protected $updatedProperties = array(
        'name' => 'metaServiceNameChanged',
        'output_format' => 'metaServiceOutputFormatChanged',
        'warning_level' => 50,
        'critical_level' => 75,
        'calculation_type' => 'Max',
        'data_source_type' => 'COUNTER',
        'selection_mode' => 1,
        'sql_like_clause_expression' => 'metaServiceExpressionChanged',
        'metric' => '',
        'check_period' => 'nonworkhours',
        'max_check_attempts' => 5,
        'normal_check_interval' => 10,
        'retry_check_interval' => 20,
        'notification_enabled' => 2,
        'contacts' => 'Guest',
        'contact_groups' => 'Supervisors',
        'notification_interval' => 12,
        'notification_period' => '24x7',
        'notification_on_warning' => 0,
        'notification_on_unknown' => 1,
        'notification_on_critical' => 0,
        'notification_on_recovery' => 1,
        'notification_on_flapping' => 0,
        'geo_coordinates' => '2.3522219,48.856614',
        'graph_template' => 'Memory',
        'enabled' => 1,
        'comments' => 'metaServiceCommentsChanged'
    );

    /**
     * @Given a meta service is configured
     */
    public function aMetaServiceIsConfigured()
    {
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I change the properties of a meta service
     */
    public function iChangeThePropertiesOfAMetaService()
    {
        $this->currentPage = new MetaServiceConfigurationListingPage($this);
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
                    $this->currentPage = new MetaServiceConfigurationListingPage($this);
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
     * @When I duplicate a meta service
     */
    public function iDuplicateAMetaService()
    {
        $this->currentPage = new MetaServiceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then the new meta service has the same properties
     */
    public function theNewMetaServiceHasTheSameProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new MetaServiceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['name'] . '_1');
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($value != $object[$key] && $key != 'name') {
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if ($value != $object[$key]) {
                                $this->tableau[] = $key;
                            }
                        }
                        if ($key == 'name' && $value . '_1' != $object[$key]) {
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
     * @When I delete a meta service
     */
    public function iDeleteAMetaService()
    {
        $this->currentPage = new MetaServiceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the deleted meta service is not displayed in the list
     */
    public function theDeletedMetaServiceIsNotDisplayedInTheList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new MetaServiceConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $host => $service) {
                    foreach ($service as $value) {
                        $bool = $bool && $value['name'] != $this->initialProperties['name'];
                    }
                }
                return $bool;
            },
            "The meta service is not being deleted.",
            5
        );
    }
}
