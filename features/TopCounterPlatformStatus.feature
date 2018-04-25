Feature: Meta-services acknowledgement
    As a Centreon Web developer
    I want to modify the top counter user interface
    To improve the Centreon user experience

    Background:
        Given I am logged in a Centreon server
        When I activate the new feature

    Scenario: Check platform status

        Then I can see the plateform status

    Scenario: Check platform status details
        When I activate the new header
        And I click on the platform icon
        Then the details popover is displayed
