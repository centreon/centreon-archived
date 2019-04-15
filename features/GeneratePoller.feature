Feature: Generate poller configuration
    As a Centreon admin
    I want to generate poller configuration easier
    To save my time

    Background:
        Given I am logged in a Centreon server

    Scenario: Generate multiple poller configuration
        Given a Centreon platform with multiple pollers
        And multiple pollers are selected
        When I click on the configuration export button
        Then I am redirected to generate page
        And the pollers are already selected

    Scenario: Generate poller configuration
        Given a Centreon platform with multiple pollers
        And multiple pollers are selected
        And I click on the configuration export button
        And I am redirected to generate page
        And the pollers are already selected
        When I click on the export button
        Then poller configuration is generated

    Scenario: Select poller to generate configuration
        Given a Centreon platform with multiple pollers
        And one poller is selected
        And I click on the configuration export button
        And I am redirected to generate page
        And I select another poller
        When I click on the export button
        Then poller configuration is generated

    Scenario: No poller selected
        Given a Centreon platform with multiple pollers
        And I click on the configuration export button
        And I am redirected to generate page
        And no poller is selected
        When I click on the export button
        Then an error message is displayed to inform that no poller is selected
