<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\ContactConfigurationPage;
use Centreon\Test\Behat\External\LoginPage;

class ControlLoginContext extends CentreonContext
{
    protected $alias = 'usertest';
    protected $name = 'usertest';
    protected $email = 'test@localhost.com';
    protected $password = 'UserPassword!1';
    protected $page;

    /**
     * @When an existing user able to connect to Centreon Web
     */
    public function anExistingUserAbleToConnectToCentreonWeb()
    {
        $this->page = new ContactConfigurationPage($this);
        $this->page->setProperties(array(
            'alias' => $this->alias,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password2' => $this->password,
            'admin' => 0
        ));
        $this->page->save();
    }

    /**
     * @When I type a wrong alias but a correct password
     */
    public function iTypeAWrongAliasButACorrectPassword()
    {
        $this->iAmLoggedOut();
        $this->page = new LoginPage($this);
        $this->assertFind('css', 'input[name="useralias"]')->setValue('falseAlias');
        $this->assertFind('css', 'input[name="password"]')->setValue($this->password);
        $this->assertFind('css', 'input[name="submitLogin"]')->click();
    }

    /**
     * @When I type a wrong password but a correct alias
     */
    public function iTypeAWrongPasswordButACorrectAlias()
    {
        $this->anExistingUserAbleToConnectToCentreonWeb();
        $this->iAmLoggedOut();
        $this->page = new LoginPage($this);
        $this->assertFind('css', 'input[name="useralias"]')->setValue($this->alias);
        $this->assertFind('css', 'input[name="password"]')->setValue('falsePassword');
        $this->assertFind('css', 'input[name="submitLogin"]')->click();
    }

    /**
     * @Then I cannot access to Centreon
     */
    public function iCannotAccessToCentreon()
    {
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has('css', 'input[name="useralias"]');
            },
            'Login failed (wrong alias/password)',
            10
        );
    }
}
