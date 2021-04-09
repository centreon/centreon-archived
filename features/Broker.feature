Feature: Centreon broker
    As a Centreon user
    I want to have an automated daemon broker configuration
    So that I will maintain my platform easily

    Background:
        Given I am logged in a Centreon server

    Scenario: Watchdog generation
        Given a configured passive service
        And a daemon broker configuration
        When I update broker configuration file name
        And I export configuration and restart centreon-broker
        Then the new configuration is applied
        And the monitoring is still working
