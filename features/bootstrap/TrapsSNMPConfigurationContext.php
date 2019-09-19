<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\SnmpTrapsConfigurationPage;
use Centreon\Test\Behat\Configuration\SnmpTrapsConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceTemplateConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostConfigurationListingPage;
use Centreon\Test\Behat\Configuration\CommandConfigurationPage;

class TrapsSNMPConfigurationContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'name' => 'atrapName',
        'oid' => '1.2.3',
        'vendor' => 'Cisco',
        'output' => 'trapOutputMessage'
    );

    protected $updatedProperties = array(
        'name' => 'atrapNameChanged',
        'oid' => '.1.2.3.4',
        'vendor' => 'HP',
        'output' => 'trapOutputMessagechanged',
        'status' => 'Critical',
        'severity' => 'serviceCategoryName (3)',
        'mode' => 1,
        'behavior' => 'If match, disable submit',
        'rule' => array(
            array(
                'string' => '@trapRule@',
                'regexp' => '/ruleRegexp/',
                'status' => 'Critical',
                'severity' => 'serviceCategoryName (3)'
            )
        ),
        'submit' => 0,
        'reschedule' => 1,
        'execute_command' => 1,
        'special_command' => 'trapCommand',
        'comments' => 'trapComments',
        'services' => 'hostName - serviceName',
        'service_templates' => 'serviceTemplateName',
        'routing' => 1,
        'routing_definition' => 'trapRouteDefinition',
        'filter_services' => 'trapFilterServices',
        'preexec' => array('trapPreexec'),
        'insert_information' => 1,
        'timeout' => '66',
        'execution_interval' => '44',
        'execution_type' => 2,
        'execution_method' => 0,
        'check_downtime' => 2,
        'output_transform' => 'trapOutputTransform',
        'custom_code' => 'trapCustomCode'
    );

    protected $duplicatedProperties = array(
        'name' => 'atrapNameChanged_1',
        'oid' => '.1.2.3.4',
        'vendor' => 'HP',
        'output' => 'trapOutputMessagechanged',
        'status' => 'Critical',
        'severity' => 'serviceCategoryName (3)',
        'mode' => 1,
        'behavior' => 'If match, disable submit',
        'rule' => array(
            array(
                'string' => '@trapRule@',
                'regexp' => '/ruleRegexp/',
                'status' => 'Critical',
                'severity' => 'serviceCategoryName (3)'
            )
        ),
        'submit' => 0,
        'reschedule' => 1,
        'execute_command' => 1,
        'special_command' => 'trapCommand',
        'comments' => 'trapComments',
        'services' => 'hostName - serviceName',
        'service_templates' => 'serviceTemplateName',
        'routing' => 1,
        'routing_definition' => 'trapRouteDefinition',
        'filter_services' => 'trapFilterServices',
        'preexec' => array('trapPreexec'),
        'insert_information' => 1,
        'timeout' => '66',
        'execution_interval' => '44',
        'execution_type' => 2,
        'execution_method' => 0,
        'check_downtime' => 2,
        'output_transform' => 'trapOutputTransform',
        'custom_code' => 'trapCustomCode'
    );

    /**
     * @When I add a new SNMP trap definition with an advanced matching rule
     */
    public function iAddANewSNMPTrapDefinitionWithAnAdvancedMatchingRule()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'hostName',
            'alias' => 'hostName',
            'address' => 'host@localhost'
        ));
        $this->currentPage->save();
        $this->currentPage = new CommandConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'command_name' => 'commandName',
            'command_line' => 'commandLine'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => 'serviceName',
            'hosts' => 'hostName',
            'check_command' => 'commandName'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceTemplateConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => $this->updatedProperties['service_templates'],
            'alias' => $this->updatedProperties['service_templates']
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'serviceCategoryName',
            'description' => 'severityDescription',
            'severity' => 1,
            'level' => '3',
            'icon' => '       centreon (png)'
        ));
        $this->currentPage->save();
        $this->currentPage = new SnmpTrapsConfigurationPage($this);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the trap definition is saved with its properties, especially the content of Regexp field
     */
    public function theTrapDefinitionIsSavedWithItsPropertiesEspeciallyTheContentOfRegexpField()
    {
        $this->tableau = array();

        $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
        try {
            $this->spin(
                function ($context) {
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($key != 'rule' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                    }
                    $i = 0;
                    $countObject = count($object['rule']);
                    foreach ($this->updatedProperties['rule'] as $value) {
                        if ($i >= $countObject || $value['string'] != $object['rule'][$i]['string']) {
                            $this->tableau[] = 'rule_string';
                        }
                        if ($i >= $countObject || $value['regexp'] != $object['rule'][$i]['regexp']) {
                            $this->tableau[] = 'rule_regexp';
                        }
                        if ($i >= $countObject || $value['status'] != $object['rule'][$i]['status']) {
                            $this->tableau[] = 'rule_status';
                        }
                        if ($i >= $countObject || $value['severity'] != $object['rule'][$i]['severity']) {
                            $this->tableau[] = 'rule_severity';
                        }
                        ++$i;
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
     * @When I modify some properties of an existing SNMP trap definition
     */
    public function iModifySomePropertiesOfAnExistingSNMPTrapDefinition()
    {
        $this->currentPage = new SnmpTrapsConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'hostName',
            'alias' => 'hostName',
            'address' => 'host@localhost'
        ));
        $this->currentPage->save();
        $this->currentPage = new CommandConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'command_name' => 'commandName',
            'command_line' => 'commandLine'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => 'serviceName',
            'hosts' => 'hostName',
            'check_command' => 'commandName'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceTemplateConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => $this->updatedProperties['service_templates'],
            'alias' => $this->updatedProperties['service_templates']
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'serviceCategoryName',
            'description' => 'severityDescription',
            'severity' => 1,
            'level' => '3',
            'icon' => '       centreon (png)'
        ));
        $this->currentPage->save();
        $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then all changes are saved
     */
    public function allChangesAreSaved()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($key != 'rule' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                    }
                    $i = 0;
                    $countObject = count($object['rule']);
                    foreach ($this->updatedProperties['rule'] as $value) {
                        if ($i >= $countObject || $value['string'] != $object['rule'][$i]['string']) {
                            $this->tableau[] = 'rule_string';
                        }
                        if ($i >= $countObject || $value['regexp'] != $object['rule'][$i]['regexp']) {
                            $this->tableau[] = 'rule_regexp';
                        }
                        if ($i >= $countObject || $value['status'] != $object['rule'][$i]['status']) {
                            $this->tableau[] = 'rule_status';
                        }
                        if ($i >= $countObject || $value['severity'] != $object['rule'][$i]['severity']) {
                            $this->tableau[] = 'rule_severity';
                        }
                        ++$i;
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
     * @When I have duplicated one existing SNMP trap definition
     */
    public function iHaveDuplicatedOneExistingSNMPTrapDefinition()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'hostName',
            'alias' => 'hostName',
            'address' => 'host@localhost'
        ));
        $this->currentPage->save();
        $this->currentPage = new CommandConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'command_name' => 'commandName',
            'command_line' => 'commandLine'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => 'serviceName',
            'hosts' => 'hostName',
            'check_command' => 'commandName'
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceTemplateConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'description' => $this->updatedProperties['service_templates'],
            'alias' => $this->updatedProperties['service_templates']
        ));
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties(array(
            'name' => 'serviceCategoryName',
            'description' => 'severityDescription',
            'severity' => 1,
            'level' => '3',
            'icon' => '       centreon (png)'
        ));
        $this->currentPage->save();
        $this->currentPage = new SnmpTrapsConfigurationPage($this);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();

        $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->updatedProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then all SNMP trap properties are updated
     */
    public function allSNMPTrapPropertiesAreUpdated()
    {
        $this->tableau = array();

        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($key != 'rule' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                    }
                    $i = 0;
                    $countObject = count($object['rule']);
                    foreach ($this->duplicatedProperties['rule'] as $value) {
                        if ($i >= $countObject || $value['string'] != $object['rule'][$i]['string']) {
                            $this->tableau[] = 'rule_string';
                        }
                        if ($i >= $countObject || $value['regexp'] != $object['rule'][$i]['regexp']) {
                            $this->tableau[] = 'rule_regexp';
                        }
                        if ($i >= $countObject || $value['status'] != $object['rule'][$i]['status']) {
                            $this->tableau[] = 'rule_status';
                        }
                        if ($i >= $countObject || $value['severity'] != $object['rule'][$i]['severity']) {
                            $this->tableau[] = 'rule_severity';
                        }
                        ++$i;
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
     * @When I have deleted one existing SNMP trap definition
     */
    public function iHaveDeletedOneExistingSNMPTrapDefinition()
    {
        $this->currentPage = new SnmpTrapsConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
        $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then this definition disappears from the SNMP trap list
     */
    public function thisDefinitionDisappearsFromTheSNMPTrapList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new SnmpTrapsConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The contact group was not deleted.",
            5
        );
    }
}
