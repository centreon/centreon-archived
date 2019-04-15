Feature: Centreon Engine Restart
    As a Centreon user
    I want to be able to restart and reload Centreon Engine
    To reload Centreon Engine's configuration

    Background:
        Given I am logged in a Centreon server

    Scenario: Check Centreon Engine Restart
        Given I am on the poller configuration export page
        And I check Restart Monitoring Engine
        And I select the method Restart
        When I export Centreon Engine
        Then Centreon Engine is restarted

    Scenario: Check Centreon Engine Reload
        Given I am on the poller configuration export page
        And I check Restart Monitoring Engine
        And I select the method Reload
        When I export Centreon Engine
        Then Centreon Engine is reloaded
