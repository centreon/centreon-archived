Feature: Save last search for filter
    As a Centreon user
    I want to have my last search in the page filter when I reopen the select2 after to have select an element
    To not retype the search but the listing is sorted after the edition and the save of a form

    Background:
       Given I am logged in a Centreon server

    Scenario: Search a string in host template
        Given a search on the host template listing
        When I change page
        And I go back on the host template listing
        Then the search is fill by the previous search

    Scenario: Search a string in traps
        Given a search on the traps listing
        When I change page
        And I go back on the traps listing
        Then the search is fill by the previous search