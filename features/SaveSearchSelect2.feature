Feature: Save last search in select2
    As a Centreon user
    I want to have my last search in select2 when I reopen the select2 after to have select an element
    To not retype the search

    Background:
       Given I am logged in a Centreon server

    Scenario: Search a string in connector command field
        Given a search on a select2
        And I close this select2
        When I reopen this select2
        Then the search is fill by the previous search
        And the elements are filtered
