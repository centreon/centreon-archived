Feature: Clapi
  As a Centreon admin
  I want to configure my centreon by command line
  To industrialize it

  Scenario: export existing configuration
    Given a Centreon server
    When the user uses the clapi export command
    Then a valid clapi configuration file should be generated
    Then it should contain the supported configuration objects

  Scenario: import from clapi command file
    Given a freshly installed Centreon server
    And a Clapi configuration file
    When the user uses the clapi import command
    Then the configuration objects should be added to the central configuration