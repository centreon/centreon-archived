Feature: Clapi
    As a Centreon admin
    I want to configure my centreon by command line
    To industrialize it

    Background:
        Given I am logged in a freshly installed Centreon server

    Scenario: import/export
        Given a configuration
        When I import this configuration
        And I export it
        Then The configuration exported is similar when it was imported
