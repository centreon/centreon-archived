Feature:
  In order to get information on the current user
  As a user
  I want retrieve those information

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Get user parameters
    Given I am logged in
    And the following CLAPI import data:
    """
    Test user;tuser;tuser@mail.com;userpassword;1;1;en_US;local"
    """
    And the configuration is generated and exported

    When I send a GET request to '/beta/configuration/users/current/parameters'
    Then the response code should be "200"
    And the JSON node "name" should be equal to the string "Test user"
    And the JSON node "alias" should be equal to the string "tuser"
    And the JSON node "email" should be equal to the string "tuser@email.com"
    And the JSON node "locale" should be equal to the string "en_US"

