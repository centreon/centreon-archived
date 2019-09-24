<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ACLMenuConfigurationPage;
use Centreon\Test\Behat\Administration\ACLMenuConfigurationListingPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationPage;
use Centreon\Test\Behat\Administration\ACLGroupConfigurationListingPage;

class ACLMenusAccessContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'acl_name' => 'aclMenu',
        'acl_alias' => 'aclMenuAlias',
        'acl_groups' => array(
            'aclGroup1',
            'aclGroup2'
        ),
        'menu_home' => 1,
        'menu_monitoring' => 1,
        'menu_reporting' => 1,
        'menu_configuration' => 1,
        'menu_administration' => 1,
        'comments' => 'aclMenuComment'
    );

    private $updatedProperties = array(
        'acl_name' => 'aclMenu_1',
        'acl_alias' => 'aclMenuAlias',
        'acl_groups' => array(
            'aclGroup1',
            'aclGroup2'
        ),
        'menu_home' => 1,
        'menu_monitoring' => 1,
        'menu_configuration' => 1,
        'menu_administration' => 1,
        'comments' => 'aclMenuComment'
    );

    private $aclGroup1 = array(
        'group_name' => 'aclGroup1',
        'group_alias' => 'aclGroup1'
    );

    private $aclGroup2 = array(
        'group_name' => 'aclGroup2',
        'group_alias' => 'aclGroup2'
    );

    private $aclGroup3 = array(
        'group_name' => 'aclGroup3',
        'group_alias' => 'aclGroup3'
    );

    /**
     * @Given three ACL access groups have been created
     */
    public function threeACLAccessGroupsHaveBeenCreated()
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
     * @When I add a new menu access linked with two groups
     */
    public function iAddANewMenuAccessLinkedWithTwoGroups()
    {
        $this->currentPage = new ACLMenuConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @Then the menu access is saved with its properties
     */
    public function theMenuAccessIsSavedWithItsProperties()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLMenuConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
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
     * @Then only chosen linked access groups display the new menu access in Authorized information tab
     */
    public function onlyChosenLinkedAccessGroupsDisplayTheNewMenuAccessInAuthorizedInformationTab()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['menu'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['menu'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup3['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['menu']) != 0) {
                        $this->tableau[] = $this->aclGroup3['group_name'];
                    }
                    return count($this->tableau) == 0;
                },
                "Some acl_group are not being correctly updated",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some acl_groups are not being correctly updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @Given one existing ACL Menu access linked with two access groups
     */
    public function oneExistingACLMenuAccessLinkedWithTwoAccessGroups()
    {
        $this->currentPage = new ACLMenuConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I remove one access group
     */
    public function iRemoveOneAccessGroup()
    {
        $this->currentPage = new ACLMenuConfigurationListingPage($this);
        $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
        $this->currentPage->setProperties(array(
            'acl_groups' => $this->aclGroup1['group_name']
        ));
        $this->currentPage->save();
    }

    /**
     * @Then link between access group and Menu access must be broken
     */
    public function linkBetweenAccessGroupAndMenuAccessMustBeBroken()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLMenuConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['acl_groups'][0] != $this->aclGroup1['group_name']) {
                        $this->tableau[] = $this->initialProperties['acl_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['menu'][0] != $this->initialProperties['acl_name']) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['menu']) != 0) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    return count($this->tableau) == 0;
                },
                "Some objects are not being updated",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some objects are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @Given one existing Menu access
     */
    public function oneExistingMenuAccess()
    {
        $this->currentPage = new ACLMenuConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I duplicate the Menu access
     */
    public function iDuplicateTheMenuAccess()
    {
        $this->currentPage = new ACLMenuConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new Menu access is created with identical properties except the name
     */
    public function aNewMenuAccessIsCreatedWithIdenticalPropertiesExceptTheName()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLMenuConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->updatedProperties['acl_name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->updatedProperties as $key => $value) {
                        if ($key != 'acl_groups' && $value != $object[$key]) {
                            var_dump($object[$key]);
                            var_dump($value);
                            $this->tableau[] = $key;
                        }
                    }
                    if (count($object['acl_groups']) != 2 || $object['acl_groups'][0] != $this->aclGroup1['group_name']
                        || $object['acl_groups'][1] != $this->aclGroup2['group_name']
                    ) {
                        $this->tableau[] = $this->updatedProperties['acl_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if ($object['menu'][0] != $this->initialProperties['acl_name']
                        || $object['menu'][1] != $this->updatedProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['menu']) != 2 || $object['menu'][0] != $this->initialProperties['acl_name']
                        || $object['menu'][1] != $this->updatedProperties['acl_name']
                    ) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    return count($this->tableau) == 0;
                },
                "Some objects are not being updated",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some objects are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @Given one existing enabled Menu access
     */
    public function oneExistingEnabledMenuAccess()
    {
        $this->currentPage = new ACLMenuConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I disable it
     */
    public function iDisableIt()
    {
        $this->currentPage = new ACLMenuConfigurationListingPage($this);
        $options = $this->getSession()->getPage()->findAll(
            'css',
            'table[class="ListTable"] tr'
        );
        foreach ($options as $element) {
            if ($this->assertFindIn($element, 'css', 'td:nth-child(2)')->getText() ==
                $this->initialProperties['acl_name']
            ) {
                $this->assertFindIn($element, 'css', 'img[src="img/icons/disabled.png"]')->click();
            }
        }
    }

    /**
     * @Then its status is modified
     */
    public function itsStatusIsModified()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ACLMenuConfigurationListingPage($this);
                $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
                return (!$object['enabled']);
            },
            "The ACL Menu is not being updated.",
            5
        );
    }

    /**
     * @When I delete the Menu access
     */
    public function iDeleteTheMenuAccess()
    {
        $this->currentPage = new ACLMenuConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['acl_name']);
        $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]')->getParent()->click();
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the menu access record is not visible anymore in Menus Access Page
     */
    public function theMenuAccessRecordIsNotVisibleAnymoreInMenusAccessPage()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ACLMenuConfigurationListingPage($this);
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
     * @Then the link with access groups is broken
     */
    public function theLinkWithAccessGroupIsBroken()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup1['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['menu']) != 0) {
                        $this->tableau[] = $this->aclGroup1['group_name'];
                    }
                    $this->currentPage = new ACLGroupConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->aclGroup2['group_name']);
                    $object = $this->currentPage->getProperties();
                    if (count($object['menu']) != 0) {
                        $this->tableau[] = $this->aclGroup2['group_name'];
                    }
                    return count($this->tableau) == 0;
                },
                "Some links to the ACL Menu are not being deleted.",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some links to the ACL Menu are not being deleted. : " . implode(',', $this->tableau));
        }
    }
}
