Feature: The Centreon homepage

  I want to open centreon homepage

  @focus
  Scenario: login with valid credentials
    Given I am on the login page
    When I fill in inputs credentials files user data
    And I press "Connect"
    Then I should see "Header content"