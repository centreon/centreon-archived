Feature: Pagination of select2
    As a Centreon admin
    I change the number of elements loaded in select
    To optimize the data presentation

    Scenario: Change the value of number of elements loaded in select
        Given I am logged in a Centreon server
        When I change the number of elements loaded in select in the configuration
        Then the value is saved
