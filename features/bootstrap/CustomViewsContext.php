<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\CustomViewsPage;
use Centreon\Test\Behat\ContactConfigurationPage;

class CustomViewsContext extends CentreonContext
{
    protected $customViewName;
    protected $newCustomViewName;
    protected $user;
    protected $owner;

    /**
     *  Build a new context.
     */
    public function __construct()
    {
        $this->customViewName = 'AcceptanceTestCustomView';
        $this->newCustomViewName = 'NewAcceptanceTestCustomView';
        $this->user = 'user1';
        $this->owner = 'admin';
    }

    /**
     *  @Given I am logged in a Centreon server with some widgets
     */
    public function iAmLoggedInCentreonWithWidgets()
    {
        $this->launchCentreonWebContainer('web_widgets');
        $this->iAmLoggedIn();

        $page = new ContactConfigurationPage($this);
        $page->setProperties(array(
            'alias' => $this->user,
            'name' => $this->user,
            'email' => 'user1@localhost',
            'password' => 'centreon',
            'password2' => 'centreon',
            'admin' => '1'
        ));

        $page->save();
    }

    /**
     *  @Given a publicly shared custom view
     */
    public function aPubliclySharedCustomView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->createNewView($this->customViewName, 2, true);
        $page->addWidget('First widget', 'Host Monitoring');
        $page->addWidget('Second widget', 'Service Monitoring');
    }

    /**
     *  @Given a shared custom view
     */
    public function aSharedCustomView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->createNewView($this->customViewName, 2);
        $page->addWidget('First widget', 'Host Monitoring');
        $page->addWidget('Second widget', 'Service Monitoring');
        $page->shareView($this->user, null, 0);
    }

    /**
     *  @Given a user is using the public view
     */
    public function aUserIsUsingThePublicView()
    {
        $this->anotherUserWishesToAddANewCustomView();
        $this->heCanAddThePublicView();

        $this->spin(
            function ($context) {
                return $this->assertFind('css', 'ul.tabs_header li.ui-state-default a')->getText() == $this->customViewName;
            },
            'Public view not load'
        );

    }

    /**
     * @Given the user is using the shared view
     */
    public function theUserIsUsingTheSharedView()
    {
        $this->anotherUserWishesToAddANewCustomView();
        $this->heCanAddTheSharedView();

        $this->spin(
            function ($context) {
                return $this->assertFind('css', 'ul.tabs_header li.ui-state-default a')->getText() == $this->customViewName;
            },
            'Shared view not load'
        );
    }

    /**
     * @Given a custom view shared in read only with a user
     */
    public function aCustomViewSharedInReadOnlyWithAUser()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->createNewView($this->customViewName, 2);
        $page->addWidget('First widget', 'Host Monitoring');
        $page->addWidget('Second widget', 'Service Monitoring');
        $page->shareView($this->user);
    }

    /**
     *  @When a user wishes to add a new custom view
     *  @When the user wishes to add a new custom view
     */
    public function anotherUserWishesToAddANewCustomView()
    {
        $this->changeUser($this->user);
    }

    /**
     *  @When he removes the shared view
     */
    public function heRemovesTheSharedView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->deleteView();
    }

    /**
     *  @When the user modifies the custom view
     */
    public function theUserModifiesTheCustomView()
    {
        $this->changeUser($this->user);

        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->editView($this->newCustomViewName, 1);


        $this->spin(
            function ($context) use ($page) {
                return $this->assertFind('css', 'ul.tabs_header li.ui-state-default a')->getText() == $this->newCustomViewName;
            },
            'View not updated by user'
        );
    }

    /**
     *  @When the owner modifies the custom view
     */
    public function theOwnerModifiesTheCustomView()
    {

        $this->changeUser($this->owner);
        $page = new CustomViewsPage($this);

        $page->showEditBar(true);

        $page->editView($this->newCustomViewName, 1);

        $this->spin(
            function ($context) use ($page) {
                return $this->assertFind('css', 'ul.tabs_header li.ui-state-default a')->getText() == $this->newCustomViewName;
            },
            'View not updated by owner'
        );
    }

    /**
     *  @When the owner removes the view
     */
    public function theOwnerRemovesTheView()
    {

        $this->changeUser($this->owner);

        $page = new CustomViewsPage($this);
        $page->showEditBar(true);

        $page->deleteView();

        $this->theViewIsNotVisibleAnymore();

    }

    /**
     *  @Then he can add the public view
     */
    public function heCanAddThePublicView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->loadView($this->customViewName);
    }

    /**
     *  @Then he can add the shared view
     */
    public function heCanAddTheSharedView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->loadView(null, $this->customViewName);
    }

    /**
     *  @Then he cannot modify the content of the shared view
     */
    public function heCannotModifyTheContentOfTheSharedView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);

        $this->spin(
            function ($context) use ($page) {
                return !$page->isCurrentViewEditable();
            },
            'Current view is modifiyable',
            30
        );
    }

    /**
     *  @Then he can modify the content of the shared view
     */
    public function heCanModifyTheContentOfTheSharedView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);

        $this->spin(
            function ($context) use ($page) {
                return $page->isCurrentViewEditable();
            },
            'Current view is not modifiyable',
            30
        );
    }

    /**
     *  @Then the view is still visible
     */
    public function theViewIsStillVisible()
    {
        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', '#tabs .tabs_header li')) == 1;
            }
        );
    }

    /**
     *  @Then the view is not visible anymore
     */
    public function theViewIsNotVisibleAnymore()
    {
        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', '#tabs .tabs_header li')) == 0;
            },
            'the view is visible anymore'
        );
    }

    /**
     *  @Then the view is not visible anymore for the user
     */
    public function theViewIsNotVisibleAnymoreForTheUser()
    {
        $this->changeUser($this->user);

        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', '#tabs .tabs_header li')) == 0;
            },
            'the view is visible anymore for the user'
        );
    }

    /**
     *  @Then the user can use the public view again
     */
    public function theUserCanUseThePublicViewAgain()
    {
        $this->heCanAddTheSharedView();
    }

    /**
     *  @Then the user can use the shared view again
     */
    public function theUserCanUseTheSharedViewAgain()
    {
        $this->theUserIsUsingTheSharedView();
    }

    /**
     *  @Then the changes are reflected on all users displaying the custom view
     */
    public function theChangesAreReflectedOnAllUsersDisplayingTheCustomView()
    {
        $this->changeUser($this->user);

        $page = new CustomViewsPage($this);
        $page->showEditBar(true);

        $this->spin(
            function ($context) {
                return ($this->assertFind('css', 'li.ui-state-default a'));
            }
        );

        if($this->assertFind('css', 'ul.tabs_header li.ui-state-default a')->getText() != $this->newCustomViewName){
            throw new Exception("View not updated");
        }
    }

    /**
     *  @Then the view is removed for all users displaying the custom view
     */
    public function theViewIsRemovedForAllUsersDisplayingTheCustomView()
    {
        $this->changeUser($this->user);

        new CustomViewsPage($this);
        $this->theViewIsNotVisibleAnymore();
    }

    /**
     *  @Then the view is removed for the owner
     */
    public function theViewIsRemovedForTheOwner()
    {
        $this->changeUser($this->owner);

        new CustomViewsPage($this);
        $this->theViewIsNotVisibleAnymore();
    }

    /**
     *  @Then the view remains visible for all users displaying the custom view
     */
    public function theViewRemainsVisibleForAllUsersDisplayingTheCustomView()
    {
        $this->changeUser($this->user);

        new CustomViewsPage($this);
        $this->theViewIsStillVisible();
    }

    private function changeUser($user)
    {
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = $user;
        $this->iAmLoggedIn();
    }
}
