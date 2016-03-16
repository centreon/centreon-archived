Feature:
    As a Centreon user
    I want to have a "Select all" button in select2
    To select multiple element at once time

    Background:
        Given I am logged in a Centreon server

    Scenario: Select all element without filter
        Given a select2
        When I click on Select all button
        Then all elements are selected

    Scenario: Select all element with filter
        Given a select2
        And enter a research
        When I click on Select all button
        Then all filtered elements are selected
