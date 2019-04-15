Feature: Clapi
  As a Centreon admin
  I want to configure my centreon by command line
  To industrialize it

  Background:
    Given a freshly installed Centreon server

  Scenario: import/export
    Given a Clapi configuration file
    And it was imported
    When I export the configuration through Clapi
    Then the exported file is similar to the imported filed
