<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\Configuration\ContactConfigurationListingPage;
use Centreon\Test\Behat\External\ListingPage;

class NonAdminContactCreationContext extends CentreonContext
{
    private $duplicatedAlias;
    private $currentPage;

    private $initialProperties = array(
        'name' => 'contactName',
        'alias' => 'contactAlias',
        'email' => 'contact@localhost',
        'password' => 'Centreon!2021',
        'password2' => 'Centreon!2021',
        'admin' => 1
    );

    public function __construct()
    {
        parent::__construct();
        $this->duplicatedAlias = 'contactAlias_1';
    }

    /**
     * @When I create a contact
     */
    public function iCreateAContact()
    {
        $this->currentPage = new ContactConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I duplicate it
     */
    public function iDuplicateIt()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @When I delete it
     */
    public function iDeleteIt()
    {
        $this->currentPage = new ContactConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['alias']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the duplicated contact is displayed in the user list
     */
    public function theDuplicatedContactIsDisplayedInTheUserList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ContactConfigurationListingPage($this);
                return $this->currentPage->getEntry($this->duplicatedAlias);
            },
            "The duplicated contact was not found.",
            5
        );
    }

    /**
     * @Then I can logg in Centreon Web with the duplicated contact
     */
    public function iCanLoggInCentreonWebWithTheDuplicatedContact()
    {
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = $this->duplicatedAlias;
        $this->parameters['centreon_password'] = $this->initialProperties['password'];
        $this->iAmLoggedIn();
    }

    /**
     * @Then the deleted contact is not displayed in the user list
     */
    public function theDeletedContactIsNotDisplayedInTheUserList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new ContactConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['alias'] != $this->initialProperties['alias'];
                }
                return $bool;
            },
            "The contact was not deleted.",
            5
        );
    }
}
