Feature: Centreon Engine Restart
    As a Centreon user
    I want to be able to restart and reload Centreon Engine
    To reload Centreon Engine's configuration 

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Check Centreon Engine Restart
        Given I am on the Central poller page
        And I check Restart Monitoring Engine
        And I select the Method Restart
        When I export Centreon Engine
        Then Centreon Engine is restarted

    Scenario: Check Centreon Engine Reload
        Given I am on the Central poller page
        And I check Restart Monitoring Engine
        And I select the Method Reload
        When I export Centreon Engine
        Then Centreon Engine is reloaded