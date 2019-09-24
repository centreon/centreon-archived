<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ACLActionConfigurationPage;
use Centreon\Test\Behat\Administration\ACLActionConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactGroupsConfigurationPage;

class ACLActionsAccessContext extends CentreonContext
{
    private $currentPage;

    private $adminUser = array(
        'alias' => 'adminUserAlias',
        'name' => 'adminUserName',
        'email' => 'admin@localhost',
        'admin' => 1
    );

    private $adminContactGroup = array(
        'name' => 'adminContactGroupName',
        'alias' => 'adminContactGroupAlias',
        'contacts' => 'adminUserName'
    );

    private $nonAdminUser = array(
        'alias' => 'nonAdminUserAlias',
        'name' => 'nonAdminUserName',
        'email' => 'nonAdmin@localhost',
        'admin' => 0
    );

    private $adminAclGroup = array(
        'group_name' => 'adminAclGroupName',
        'group_alias' => 'adminAclGroupAlias',
        'contactgroups' => array(
            'adminContactGroupName'
        )
    );

    private $nonAdminAclGroup = array(
        'group_name' => 'nonAdminAclGroupName',
        'group_alias' => 'nonAdminAclGroupAlias',
        'contacts' => array(
            'nonAdminUserName'
        )
    );

    private $initialProperties = array(
        'acl_name' => 'aclActionName',
        'acl_alias' => 'aclActionAlias',
        'acl_groups' => array(
            'adminAclGroupName',
            'nonAdminAclGroupName'
        ),
        'action_top_counter_overview' => 1,
        'action_top_counter_poller' => 1,
        'action_poller_listing' => 1,
        'action_generate_configuration' => 1,
        'action_generate_trap' => 0,
        'action_engine' => 0,
        'action_shutdown' => 1,
        'action_restart' => 0,
        'action_notifications' => 0,
        'action_global_service_checks' => 1,
        'action_global_service_passive_checks' => 1,
        'action_global_host_checks' => 0,
        'action_global_host_passive_checks' => 0,
        'action_event_handler' => 0,
        'action_flap_detection' => 1,
        'action_global_service_obsess' => 1,
        'action_global_host_obsess' => 0,
        'action_perf_data' => 0,
        'action_service' => 0,
        'action_service_notifications' => 0,
        'action_service_acknowledgement' => 0,
        'action_service_disacknowledgement' => 1,
        'action_service_schedule_check' => 1,
        'action_service_schedule_forced_check' => 0,
        'action_service_schedule_downtime' => 0,
        'action_service_comment' => 0,
        'action_service_event_handler' => 0,
        'action_service_flap_detection' => 1,
        'action_service_submit_result' => 0,
        'action_service_display_command' => 0,
        'action_host' => 0,
        'action_host_notifications' => 1,
        'action_host_acknowledgement' => 1,
        'action_host_disacknowledgement' => 0,
        'action_host_schedule_check' => 0,
        'action_host_schedule_forced_check' => 1,
        'action_host_schedule_downtime' => 1,
        'action_host_comment' => 1,
        'action_host_event_handler' => 1,
        'action_host_flap_detection' => 0,
        'action_host_checks_for_services' => 1,
        'action_host_notifications_for_services' => 0,
        'action_name_submit_result' => 1,
        'enabled' => 1
    );

    private $duplicatedProperties = array(
        'acl_name' => 'aclActionName_1',
        'acl_alias' => 'aclActionAlias',
        'acl_groups' => array(
            'adminAclGroupName',
            'nonAdminAclGroupName'
        ),
        'action_top_counter_overview' => 1,
        'action_top_counter_poller' => 1,
        'action_poller_listing' => 1,
        'action_generate_configuration' => 1,
        'action_generate_trap' => 0,
        'action_engine' => 0,
        'action_shutdown' => 1,
        'action_restart' => 0,
        'action_notifications' => 0,
        'action_global_service_checks' => 1,
        'action_global_service_passive_checks' => 1,
        'action_global_host_checks' => 0,
        'action_global_host_passive_checks' => 0,
        'action_event_handler' => 0,
        'action_flap_detection' => 1,
        'action_global_service_obsess' => 1,
        'action_global_host_obsess' => 0,
        'action_perf_data' => 0,
        'action_service' => 0,
        'action_service_notifications' => 0,
        'action_service_acknowledgement' => 0,
        'action_service_disacknowledgement' => 1,
        'action_service_schedule_check' => 1,
        'action_service_schedule_forced_check' => 0,
        'action_service_schedule_downtime' => 0,
        'action_service_comment' => 0,
        'action_service_event_handler' => 0,
        'action_service_flap_detection' => 1,
        'action_service_submit_result' => 0,
        'action_service_display_command' => 0,
        'action_host' => 0,
        'action_host_notifications' => 1,
        'action_host_acknowledgement' => 1,
        'action_host_disacknowledgement' => 0,
        'action_host_schedule_check' => 0,
        'action_host_schedule_forced_check' => 1,
        'action_host_schedule_downtime' => 1,
        'action_host_comment' => 1,
        'action_host_event_handler' => 1,
        'action_host_flap_detection' => 0,
        'action_host_checks_for_services' => 1,
        'action_host_notifications_for_services' => 0,
        'action_name_submit_result' => 1,
        'enabled' => 1
    );

    private $updatedProperties = array(
        'acl_name' => 'aclActionNameChanged',
        'acl_alias' => 'aclActionAliasChanged',
        'acl_groups' => array(
            'nonAdminAclGroupName'
        ),
        'action_top_counter_overview' => 0,
        'action_top_counter_poller' => 1,
        'action_poller_listing' => 1,
        'action_generate_configuration' => 0,
        'action_generate_trap' => 1,
        'action_engine' => 0,
        'action_shutdown' => 1,
        'action_restart' => 1,
        'action_notifications' => 0,
        'action_global_service_checks' => 1,
        'action_global_service_passive_checks' => 0,
        'action_global_host_checks' => 1,
        'action_global_host_passive_checks' => 1,
        'action_event_handler' => 0,
        'action_flap_detection' => 1,
        'action_global_service_obsess' => 0,
        'action_global_host_obsess' => 1,
        'action_perf_data' => 1,
        'action_service' => 0,
        'action_service_notifications' => 1,
        'action_service_acknowledgement' => 0,
        'action_service_disacknowledgement' => 1,
        'action_service_schedule_check' => 0,
        'action_service_schedule_forced_check' => 1,
        'action_service_schedule_downtime' => 0,
        'action_service_comment' => 1,
        'action_service_event_handler' => 0,
        'action_service_flap_detection' => 1,
        'action_service_submit_result' => 0,
        'action_service_display_command' => 0,
        'action_host' => 0,
        'action_host_notifications' => 1,
        'action_host_acknowledgement' => 0,
        'action_host_disacknowledgement' => 0,
        'action_host_schedule_check' => 1,
        'action_host_schedule_forced_check' => 1,
        'action_host_schedule_downtime' => 1,
        'action_host_comment' => 0,
        'action_host_event_handler' => 1,
        'action_host_flap_detection' => 1,
        'action_host_checks_for_services' => 1,
        'action_host_notifications_for_services' => 0,
        'action_name_submit_result' => 0,
        'enabled' => 0
    );

    private $allSelected = array(
        'acl_name' => 'aclActionName',
        'acl_alias' => 'aclActionAlias',
        'acl_groups' => array(
            'adminAclGroupName',
            'nonAdminAclGroupName'
        ),
        'action_top_counter_overview' => 1,
        'action_top_counter_poller' => 1,
        'action_poller_listing' => 1,
        'action_generate_configuration' => 1,
        'action_generate_trap' => 1,
        'action_engine' => 0,
        'action_shutdown' => 1,
        'action_restart' => 1,
        'action_notifications' => 1,
        'action_global_service_checks' => 1,
        'action_global_service_passive_checks' => 1,
        'action_global_host_checks' => 1,
        'action_global_host_passive_checks' => 1,
        'action_event_handler' => 1,
        'action_flap_detection' => 1,
        'action_global_service_obsess' => 1,
        'action_global_host_obsess' => 1,
        'action_perf_data' => 1,
        'action_service' => 0,
        'action_service_notifications' => 1,
        'action_service_acknowledgement' => 1,
        'action_service_disacknowledgement' => 1,
        'action_service_schedule_check' => 1,
        'action_service_schedule_forced_check' => 1,
        'action_service_schedule_downtime' => 1,
        'action_service_comment' => 1,
        'action_service_event_handler' => 1,
        'action_service_flap_detection' => 1,
        'action_service_submit_result' => 1,
        'action_service_display_command' => 1,
        'action_host' => 0,
        'action_host_notifications' => 1,
        'action_host_acknowledgement' => 1,
        'action_host_disacknowledgement' => 1,
        'action_host_schedule_check' => 1,
        'action_host_schedule_forced_check' => 1,
        'action_host_schedule_downtime' => 1,
        'action_host_comment' => 1,
        'action_host_event_handler' => 1,
        'action_host_flap_detection' => 1,
        'action_host_checks_for_services' => 1,
        'action_host_notifications_for_services' => 1,
        'action_name_submit_result' => 1,
        'enabled' => 1
    );

    private $selectActionEngine = array(
        'acl_name' => 'testActionEngineName',
        'acl_alias' => 'testActionEngineAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_engine' => 1
    );

    private $checkActionEngine = array(
        'acl_name' => 'testActionEngineName',
        'acl_alias' => 'testActionEngineAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_top_counter_overview' => 0,
        'action_top_counter_poller' => 0,
        'action_poller_listing' => 0,
        'action_generate_configuration' => 0,
        'action_generate_trap' => 0,
        'action_engine' => 0,
        'action_shutdown' => 1,
        'action_restart' => 1,
        'action_notifications' => 1,
        'action_global_service_checks' => 1,
        'action_global_service_passive_checks' => 1,
        'action_global_host_checks' => 1,
        'action_global_host_passive_checks' => 1,
        'action_event_handler' => 1,
        'action_flap_detection' => 1,
        'action_global_service_obsess' => 1,
        'action_global_host_obsess' => 1,
        'action_perf_data' => 1,
        'action_service' => 0,
        'action_service_notifications' => 0,
        'action_service_acknowledgement' => 0,
        'action_service_disacknowledgement' => 0,
        'action_service_schedule_check' => 0,
        'action_service_schedule_forced_check' => 0,
        'action_service_schedule_downtime' => 0,
        'action_service_comment' => 0,
        'action_service_event_handler' => 0,
        'action_service_flap_detection' => 0,
        'action_service_submit_result' => 0,
        'action_service_display_command' => 0,
        'action_host' => 0,
        'action_host_notifications' => 0,
        'action_host_acknowledgement' => 0,
        'action_host_disacknowledgement' => 0,
        'action_host_schedule_check' => 0,
        'action_host_schedule_forced_check' => 0,
        'action_host_schedule_downtime' => 0,
        'action_host_comment' => 0,
        'action_host_event_handler' => 0,
        'action_host_flap_detection' => 0,
        'action_host_checks_for_services' => 0,
        'action_host_notifications_for_services' => 0,
        'action_name_submit_result' => 0,
        'enabled' => 1
    );

    private $selectActionService = array(
        'acl_name' => 'testActionServiceName',
        'acl_alias' => 'testActionServiceAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_service' => 1
    );

    private $checkActionService = array(
        'acl_name' => 'testActionServiceName',
        'acl_alias' => 'testActionServiceAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_top_counter_overview' => 0,
        'action_top_counter_poller' => 0,
        'action_poller_listing' => 0,
        'action_generate_configuration' => 0,
        'action_generate_trap' => 0,
        'action_engine' => 0,
        'action_shutdown' => 0,
        'action_restart' => 0,
        'action_notifications' => 0,
        'action_global_service_checks' => 0,
        'action_global_service_passive_checks' => 0,
        'action_global_host_checks' => 0,
        'action_global_host_passive_checks' => 0,
        'action_event_handler' => 0,
        'action_flap_detection' => 0,
        'action_global_service_obsess' => 0,
        'action_global_host_obsess' => 0,
        'action_perf_data' => 0,
        'action_service' => 0,
        'action_service_notifications' => 1,
        'action_service_acknowledgement' => 1,
        'action_service_disacknowledgement' => 1,
        'action_service_schedule_check' => 1,
        'action_service_schedule_forced_check' => 1,
        'action_service_schedule_downtime' => 1,
        'action_service_comment' => 1,
        'action_service_event_handler' => 1,
        'action_service_flap_detection' => 1,
        'action_service_submit_result' => 1,
        'action_service_display_command' => 1,
        'action_host' => 0,
        'action_host_notifications' => 0,
        'action_host_acknowledgement' => 0,
        'action_host_disacknowledgement' => 0,
        'action_host_schedule_check' => 0,
        'action_host_schedule_forced_check' => 0,
        'action_host_schedule_downtime' => 0,
        'action_host_comment' => 0,
        'action_host_event_handler' => 0,
        'action_host_flap_detection' => 0,
        'action_host_checks_for_services' => 0,
        'action_host_notifications_for_services' => 0,
        'action_name_submit_result' => 0,
        'enabled' => 1
    );

    private $selectActionHost = array(
        'acl_name' => 'testActionHostName',
        'acl_alias' => 'testActionHostAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_host' => 1
    );

    private $checkActionHost = array(
        'acl_name' => 'testActionHostName',
        'acl_alias' => 'testActionHostAlia',
        'acl_groups' => array(
            'adminAclGroupName'
        ),
        'action_top_counter_overview' => 0,
        'action_top_counter_poller' => 0,
        'action_poller_listing' => 0,
        'action_generate_configuration' => 0,
        'action_generate_trap' => 0,
        'action_engine' => 0,
        'action_shutdown' => 0,
        'action_restart' => 0,
        'action_notifications' => 0,
        'action_global_service_checks' => 0,
        'action_global_service_passive_checks' => 0,
        'action_global_host_checks' => 0,
        'action_global_host_passive_checks' => 0,
        'action_event_handler' => 0,
        'action_flap_detection' => 0,
        'action_global_service_obsess' => 0,
        'action_global_host_obsess' => 0,
        'action_perf_data' => 0,
        'action_service' => 0,
        'action_service_notifications' => 0,
        'action_service_acknowledgement' => 0,
        'action_service_disacknowledgement' => 0,
        'action_service_schedule_check' => 0,
        'action_service_schedule_forced_check' => 0,
        'action_service_schedule_downtime' => 0,
        'action_service_comment' => 0,
        'action_service_event_handler' => 0,
        'action_service_flap_detection' => 0,
        'action_service_submit_result' => 0,
        'action_service_display_command' => 0,
        'action_host' => 0,
        'action_host_notifications' => 1,
        'action_host_acknowledgement' => 1,
        'action_host_disacknowledgement' => 1,
        'action_host_schedule_check' => 1,
        'action_host_schedule_forced_check' => 1,
        'action_host_schedule_downtime' => 1,
        'action_host_comment' => 1,
        'action_host_event_handler' => 1,
        'action_host_flap_detection' => 1,
        'action_host_checks_for_services' => 1,
        'action_host_notifications_for_services' => 1,
        'action_name_submit_result' => 1,
        'enabled' => 1
    );

    /**
     * @Given one ACL access group including a non admin user exists
     */
    public function oneACLAccessGroupIncludingANonAdminUserExists()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->nonAdminUser);
        $this->currentPage->save();
        $this->currentPage = new ContactGroupsConfigurationPage($this);
        $this->currentPage->setProperties($this->adminContactGroup);
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->nonAdminAclGroup);
        $this->currentPage->save();
    }

    /**
     * @Given one ACL access group linked to a contact group including an admin user exists
     */
    public function oneACLAccessGroupLinkedToAContactGroupIncludingAnAdminUserExists()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->adminUser);
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->adminAclGroup);
        $this->currentPage->save();
    }

    /**
     * @When I add a new action access linked with the access groups
     */
    public function iAddANewActionAccessLinkedWithTheAccessGroups()
    {
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the action access record is saved with its properties
     */
    public function theActionAccessRecordIsSavedWithItsProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'acl_group' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'acl_group') {
                            if ($object[$key] != $value) {
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
     * @Then all linked access group display the new actions access in authorized information tab
     */
    public function allLinkedAccessGroupDisplayTheNewActionsAccessInAuthorizedInformationTab()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->adminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) == 1 &&
                        $object['actions'][0] != $this->initialProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->adminAclGroup['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->nonAdminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) == 1 &&
                        $object['actions'][0] != $this->initialProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->nonAdminAclGroup['group_name'];
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
     * @When I select one by one all action to authorize them in a action access record I create
     */
    public function iSelectOneByOneAllActionToAuthorizeThemInAActionAccessRecordICreate()
    {
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->allSelected);
        $this->currentPage->save();
    }

    /**
     * @Then all radio-buttons have to be checked
     */
    public function allRadioButtonsHaveToBeChecked()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->allSelected['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->allSelected as $key => $value) {
                        if ($key != 'acl_group' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'acl_group') {
                            if ($object[$key] != $value) {
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
     * @When I check button-radio for a lot of actions
     */
    public function iCheckButtonRadioForALotOfActions()
    {
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->selectActionEngine);
        $this->currentPage->save();
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->selectActionService);
        $this->currentPage->save();
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->selectActionHost);
        $this->currentPage->save();
    }

    /**
     * @Then all buttons-radio of the authorized actions lot are checked
     */
    public function allButtonsRadioOfTheAuthorizedActionsLotAreChecked()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->checkActionEngine['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->checkActionEngine as $key => $value) {
                        if ($key != 'acl_group' && $value != $object[$key]) {
                            $this->tableau[] = 'test action_engine : ' . $key;
                        }
                        if ($key == 'acl_group') {
                            if ($object[$key] != $value) {
                                $this->tableau[] = 'test action_engine : ' . $key;
                            }
                        }
                    }
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->checkActionService['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->checkActionService as $key => $value) {
                        if ($key != 'acl_group' && $value != $object[$key]) {
                            $this->tableau[] = 'test action_service : ' . $key;
                        }
                        if ($key == 'acl_group') {
                            if ($object[$key] != $value) {
                                $this->tableau[] = 'test action_service : ' . $key;
                            }
                        }
                    }
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->checkActionHost['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->checkActionHost as $key => $value) {
                        if ($key != 'acl_group' && $value != $object[$key]) {
                            $this->tableau[] = 'test action_host : ' . $key;
                        }
                        if ($key == 'acl_group') {
                            if ($object[$key] != $value) {
                                $this->tableau[] = 'test action_host : ' . $key;
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
     * @Given one existing action access
     */
    public function oneExistingActionAccess()
    {
        $this->currentPage = new ACLActionConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I remove the access group
     */
    public function iRemoveTheAccessGroup()
    {
        $this->currentPage = new ACLActionConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
        $this->currentPage->setProperties(array(
            'acl_groups' => array(
                'nonAdminAclGroupName'
            )
        ));
        $this->currentPage->save();
    }

    /**
     * @Then link between access group and action access must be broken
     */
    public function linkBetweenAccessGroupAndActionAccessMustBeBroken()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['acl_groups'] != array('nonAdminAclGroupName')) {
                        $this->tableau[] = 'acl_groups';
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->adminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 0) {
                        $this->tableau[] = $this->adminAclGroup['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->nonAdminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 1 &&
                        $object['actions'][0] != $this->initialProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->nonAdminAclGroup['group_name'];
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
     * @When I duplicate the action access
     */
    public function iDuplicateTheActionAccess()
    {
        $this->currentPage = new ACLActionConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new action access record is created with identical properties except the name
     */
    public function aNewActionAccessRecordIsCreatedWithIdenticalPropertiesExceptTheName()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($key != 'acl_groups' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'acl_groups') {
                            if ($object[$key] != $value) {
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
     * @When I modify some properties such as name, description, comments, status or authorized actions
     */
    public function iModifySomePropertiesSuchAsNameDescriptionCommentsStatusOrAuthorizedActions()
    {
        $this->currentPage = new ACLActionConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
        $this->currentPage->setProperties($this->updatedProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the modifications are saved
     */
    public function theModificationsAreSaved()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLActionConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['acl_groups'] != array('nonAdminAclGroupName')) {
                        $this->tableau[] = 'acl_groups';
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->adminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 0) {
                        $this->tableau[] = $this->adminAclGroup['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->nonAdminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 1 &&
                        $object['actions'][0] != $this->initialProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->nonAdminAclGroup['group_name'];
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
     * @When I delete the action access
     */
    public function iDeleteTheActionAccess()
    {
        $this->currentPage = new ACLActionConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the action access record is not visible anymore in action access page
     */
    public function theActionAccessRecordIsNotVisibleAnymoreInActionAccessPage()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ACLActionConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['acl_name'];
                }
                return $bool;
            },
            "The ACL Menu is not being deleted.",
            5
        );
    }

    /**
     * @Then the links with the acl groups are broken
     */
    public function theLinksWithTheAclGroupsAreBroken()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->adminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 0) {
                        $this->tableau[] = $this->adminAclGroup['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->nonAdminAclGroup['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['actions']) != 0) {
                        $this->tableau[] = $this->nonAdminAclGroup['group_name'];
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
