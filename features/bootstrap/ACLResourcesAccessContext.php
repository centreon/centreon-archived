<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ACLResourceConfigurationPage;
use Centreon\Test\Behat\Administration\ACLResourceConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationListingPage;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\HostGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\HostCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceGroupConfigurationPage;
use Centreon\Test\Behat\Configuration\ServiceCategoryConfigurationPage;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;

class ACLResourcesAccessContext extends CentreonContext
{
    protected $currentPage;

    protected $initialProperties = array(
        'acl_name' => 'aclResourceName',
        'acl_alias' => 'aclResourceAlias',
        'acl_groups' => array(
            'aclGroupName1',
            'aclGroupName2'
        ),
        'enabled' => 1,
        'comments' => 'aclResourceComment',
        'all_hosts' => 0,
        'hosts' => 'hostName1',
        'all_hostgroups' => 0,
        'host_groups' => 'hostGroupName',
        'excluded_hosts' => 'hostName2',
        'all_servicegroups' => 0,
        'service_groups' => 'serviceGroupName',
        'meta_services' => 'metaServiceName',
        'pollers' => 'Central',
        'host_category' => 'hostCategoryName',
        'service_category' => 'serviceCategoryName'
    );

    protected $duplicatedProperties = array(
        'acl_name' => 'aclResourceName_1',
        'acl_alias' => 'aclResourceAlias',
        'acl_groups' => array(
            'aclGroupName1',
            'aclGroupName2'
        ),
        'enabled' => 1,
        'comments' => 'aclResourceComment',
        'all_hosts' => 0,
        'hosts' => 'hostName1',
        'all_hostgroups' => 0,
        'host_groups' => 'hostGroupName',
        'excluded_hosts' => 'hostName2',
        'all_servicegroups' => 0,
        'service_groups' => 'serviceGroupName',
        'meta_services' => 'metaServiceName',
        'pollers' => 'Central',
        'host_category' => 'hostCategoryName',
        'service_category' => 'serviceCategoryName'
    );

    protected $updatedProperties = array(
        'acl_name' => 'aclResourceNameChanged',
        'acl_alias' => 'aclResourceAliasChanged',
        'acl_groups' => array(
            'aclGroupName3',
            'aclGroupName2'
        ),
        'enabled' => 0,
        'comments' => 'aclResourceCommentChanged',
        'all_hosts' => 0,
        'hosts' => 'hostName1',
        'all_hostgroups' => 0,
        'host_groups' => 'hostGroupName',
        'excluded_hosts' => 'hostName2',
        'all_servicegroups' => 0,
        'service_groups' => 'serviceGroupName',
        'meta_services' => 'metaServiceName',
        'pollers' => 'Central',
        'host_category' => 'hostCategoryName',
        'service_category' => 'serviceCategoryName'
    );

    protected $host1 = array(
        'name' => 'hostName1',
        'alias' => 'hostAlias1',
        'address' => 'host1@localhost'
    );

    protected $host2 = array(
        'name' => 'hostName2',
        'alias' => 'hostAlias2',
        'address' => 'host2@localhost'
    );

    protected $hostGroup = array(
        'name' => 'hostGroupName',
        'alias' => 'hostGroupAlias'
    );

    protected $hostCategory = array(
        'name' => 'hostCategoryName',
        'alias' => 'hostCategoryAlias'
    );

    protected $serviceGroup = array(
        'name' => 'serviceGroupName',
        'description' => 'serviceGroupDescription'
    );

    protected $serviceCategory = array(
        'name' => 'serviceCategoryName',
        'description' => 'serviceCategoryDescription'
    );

    protected $metaService = array(
        'name' => 'metaServiceName',
        'max_check_attempts' => '5'
    );

    protected $aclGroup1 = array(
        'group_name' => 'aclGroupName1',
        'group_alias' => 'aclGroupAlias1'
    );

    protected $aclGroup2 = array(
        'group_name' => 'aclGroupName2',
        'group_alias' => 'aclGroupAlias2'
    );

    protected $aclGroup3 = array(
        'group_name' => 'aclGroupName3',
        'group_alias' => 'aclGroupAlias3'
    );

    protected $linkedAclResource = array(
        'acl_name' => 'aclResourceName',
        'acl_groups' => array(
            'aclGroupName1',
            'aclGroupName2'
        )
    );

    /**
     * @Given three ACL access groups including non admin users exist
     */
    public function threeACLAccessGroupsIncludingNonAdminUsersExist()
    {
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->aclGroup1);
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->aclGroup2);
        $this->currentPage->save();
        $this->currentPage = new ACLGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->aclGroup3);
        $this->currentPage->save();
    }

    /**
     * @When I add a new Resources access linked with two groups
     */
    public function iAddANewResourcesAccessLinkedWithTwoGroups()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup);
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService);
        $this->currentPage->save();
        $this->currentPage = new ACLResourceConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the Resources access is saved with its properties
     */
    public function theResourcesAccessIsSavedWithItsProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLResourceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'acl_groups' && $value != $object[$key]) {
                            if ($value != $object[$key][0]) {
                                $this->tableau[] = $key;
                            }
                        }
                        if ($key == 'acl_groups') {
                            if (count($object[$key]) != 0 && $object[$key][0] != $this->aclGroup1['group_name']
                                && $object[$key][1] != $this->aclGroup2['group_name']
                            ) {
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
     * @Then only chosen linked access groups display the new Resources access in Authorized information tab
     */
    public function onlyChosenLinkedAccessGroupsDisplayTheNewResourcesAccessInAuthorizedInformationTab()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['resources'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['resources'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup3['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) != 0) {
                        $this->tableau[] = $this->aclGroup3['group_name'];
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
     * @Given one existing Resources access linked with two access groups
     */
    public function oneExistingResourcesAccessLinkedWithTwoAccessGroups()
    {
        $this->currentPage = new ACLResourceConfigurationPage($this);
        $this->currentPage->setProperties($this->linkedAclResource);
        $this->currentPage->save();
    }

    /**
     * @When I remove one access group
     */
    public function iRemoveOneAccessGroup()
    {
        $this->currentPage = new ACLResourceConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->linkedAclResource['acl_name']);
        $this->currentPage->setProperties(array(
            'acl_groups' => array(
                'aclGroupName1'
            )
        ));
        $this->currentPage->save();
    }

    /**
     * @Then link between access group and Resources access must be broken
     */
    public function linkBetweenAccessGroupAndResourcesAccessMustBeBroken()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLResourceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->linkedAclResource['acl_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['acl_groups']) != 1 ||
                        $object['acl_groups'][0] != $this->aclGroup1['group_name']
                    ) {
                        $this->tableau[] = $this->linkedAclResource['acl_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['resources'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) != 0) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup3['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) != 0) {
                        $this->tableau[] = $this->aclGroup3['group_name'];
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
     * @Given one existing Resources access
     */
    public function oneExistingResourcesAccess()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup);
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService);
        $this->currentPage->save();
        $this->currentPage = new ACLResourceConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I duplicate the Resources access
     */
    public function iDuplicateTheResourcesAccess()
    {
        $this->currentPage = new ACLResourceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new Resources access record is created with identical properties except the name
     */
    public function aNewResourcesAccessRecordIsCreatedWithIdenticalPropertiesExceptTheName()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLResourceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($key != 'acl_groups' && $value != $object[$key]) {
                            if ($value != $object[$key][0]) {
                                $this->tableau[] = $key;
                            }
                        }
                        if ($key == 'acl_groups') {
                            if (count($object[$key]) != 0 && $object[$key][0] != $this->aclGroup1['group_name']
                                && $object[$key][1] != $this->aclGroup2['group_name']
                            ) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) == 2
                        && $object['resources'][0] != $this->initialProperties['acl_name']
                        && $object['resources'][1] != $this->duplicatedProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) == 2
                        && $object['resources'][0] != $this->initialProperties['acl_name']
                        && $object['resources'][1] != $this->duplicatedProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup3['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) != 0) {
                        $this->tableau[] = $this->aclGroup3['group_name'];
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
     * @Given one existing enabled Resources access record
     */
    public function oneExistingEnabledResourcesAccessRecord()
    {
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host1);
        $this->currentPage->save();
        $this->currentPage = new HostConfigurationPage($this);
        $this->currentPage->setProperties($this->host2);
        $this->currentPage->save();
        $this->currentPage = new HostGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->hostGroup);
        $this->currentPage->save();
        $this->currentPage = new HostCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->hostCategory);
        $this->currentPage->save();
        $this->currentPage = new ServiceGroupConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceGroup);
        $this->currentPage->save();
        $this->currentPage = new ServiceCategoryConfigurationPage($this);
        $this->currentPage->setProperties($this->serviceCategory);
        $this->currentPage->save();
        $this->currentPage = new MetaServiceConfigurationPage($this);
        $this->currentPage->setProperties($this->metaService);
        $this->currentPage->save();
        $this->currentPage = new ACLResourceConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I modify some properties such as name, description, comments or status
     */
    public function iModifySomePropertiesSuchAsNameDescriptionCommentsOrStatus()
    {
        $this->currentPage = new ACLResourceConfigurationPage($this);
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
                    $this->currentPage = new ACLResourceConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($key != 'acl_groups' && $value != $object[$key]) {
                            if ($value != $object[$key][0]) {
                                $this->tableau[] = $key;
                            }
                        }
                        if ($key == 'acl_groups') {
                            if (count($object[$key]) != 0 && $object[$key][0] != $this->aclGroup2['group_name']
                                && $object[$key][1] != $this->aclGroup3['group_name']
                            ) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup3['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['resources'][0] != $this->updatedProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup3['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) == 2
                        && $object['resources'][0] != $this->initialProperties['acl_name']
                        && $object['resources'][1] != $this->updatedProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['resources']) != 1
                        && $object['resources'][0] != $this->initialProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
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
     * @When I delete the Resources access
     */
    public function iDeleteResourcesAccess()
    {
        $this->currentPage = new ACLResourceConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the Resources access record is not visible anymore in Resources Access page
     */
    public function theResourcesAccessRecordIsNotVisibleAnymoreInResourcesAccessPage()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ACLResourceConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['acl_name'];
                }
                return $bool;
            },
            "The ACL Resource is not being deleted.",
            5
        );
    }
}
