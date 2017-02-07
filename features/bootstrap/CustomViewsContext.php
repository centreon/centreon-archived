<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\CustomViewsPage;
use Centreon\Test\Behat\ContactConfigurationPage;

class CustomViewsContext extends CentreonContext
{
    private $customViewName;
    private $user;

    /**
     *  Build a new context.
     */
    public function __construct()
    {
        $this->customViewName = 'AcceptanceTestCustomView';
        $this->user = 'user1';
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
        $page->shareView($this->user);
    }

    /**
     *  @Given a user is using the public view
     */
    public function aUserIsUsingThePublicView()
    {
        $this->anotherUserWishesToAddANewCustomView();
        $this->heCanAddThePublicView();
    }

    /**
     *  @Given the user is using the shared view
     */
    public function theUserIsUsingTheSharedView()
    {
        $this->anotherUserWishesToAddANewCustomView();
        $this->heCanAddTheSharedView();
    }

    /**
     *  @Given a custom view shared in read only with a user
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
        $this->iAmLoggedOut();
        $this->parameters['centreon_user'] = $this->user ;
        $this->iAmLoggedIn();
    }

    /**
     *  @When he removes the shared view
     */
    public function heRemovesTheSharedView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar();
        $page->deleteView();
    }

    /**
     *  @When the owner modifies the custom view
     */
    public function theOwnerModifiesTheCustomView()
    {
        // XXX
    }

    /**
     *  @When the owner removes the view
     */
    public function theOwnerRemovesTheView()
    {
        // XXX
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
            function ($context) {
                return ($this->assertFind('css', 'button.editView')->getAttribute('aria-disabled'));
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
            }
        );
    }

    /**
     *  @Then the user can use the public view again
     */
    public function theUserCanUseThePublicViewAgain()
    {
        $this->aUserIsUsingThePublicView();
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
        // XXX
    }

    /**
     *  @Then the view is removed for all users displaying the custom view
     */
    public function theViewIsRemovedForAllUsersDisplayingTheCustomView()
    {
        // XXX
    }
}
