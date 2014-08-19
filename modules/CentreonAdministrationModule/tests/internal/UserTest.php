<?php

namespace Test\Centreon;

use \CentreonAdministration\Internal\User;
use \Centreon\Internal\Di;

class UserTest extends DbTestCase
{
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = new User(1);
    }

    public function testGetId()
    {
        $this->assertEquals(1, $this->user->getId());
    }

    public function testGetName()
    {
        $this->assertEquals('John Doe', $this->user->getName());
    }

    public function testGetLogin()
    {
        $this->assertEquals('jdoe', $this->user->getLogin());
    }

    public function testGetEmail()
    {
        $this->assertEquals('jdoe@localhost', $this->user->getEmail());
    }

    public function testIsAdmin()
    {
        $this->assertEquals(0, $this->user->isAdmin());
    }

    public function testGetHomePage()
    {
        $this->assertEquals('/customview', $this->user->getHomePage());
    }
}
