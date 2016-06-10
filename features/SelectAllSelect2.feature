Feature: Select all in select2
    As a Centreon user
    I want to have a "Select all" button in select2
    To select multiple element at once time

    Background:
        Given I am logged in a Centreon server

    Scenario: Select all element without filter
        Given a select2
        When I click on Select all button
        And I validate Select all confirm box
        Then all elements are selected

    Scenario: Select all element with filter
        Given a select2
        And I search "host" in Select
        When I click on Select all button
        And I validate Select all confirm box
        Then all filtered elements are selected

    Scenario: Remove popin on click on cancel
        Given a select2
        When I click on Select all button
        And I cancel Select all confirm box
        Then no one element is selected

    Scenario: Remove popin on click on cross
        Given a select2
        When I click on Select all button
        And I exit Select all confirm box
        Then no one element is selected
