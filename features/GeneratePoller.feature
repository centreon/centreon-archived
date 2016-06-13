Feature: Generate poller configuration
    As a Centreon admin
    I want to generate poller configuration easier
    To save my time

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Generate multiple poller configuration
        Given a Centreon platform with multiple pollers
        And multiple pollers are selected
        When I click on the button "apply_configuration"
        Then I am redirected to generate page
        And the pollers are already selected

    Scenario: Generate poller configuration
        Given a Centreon platform with multiple pollers
        And multiple pollers are selected
        And I click on the button "apply_configuration"
        And I am redirected to generate page
        When I click on the button "submit"
        Then poller configuration is generated

    Scenario: Select poller to generate configuration
        Given a Centreon platform with multiple pollers
        And one poller is selected
        And I click on the button "apply_configuration"
        And I am redirected to generate page
        And I select an other poller
        When I click on the button "submit"
        Then poller configuration is generated

    Scenario: No poller selected
        Given a Centreon platform with multiple pollers
        And I click on the button "apply_configuration"
        And I am redirected to generate page
        And no one poller is selected
        When I click on the button "submit"
        Then an error message is displayed to inform that no one poller is selected

