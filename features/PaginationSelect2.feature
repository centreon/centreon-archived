Feature:
    As a Centreon admin
    I change the number of elements loaded in select
    To optimize the data presentation

    Scenario: Change the value of number of elements loaded in select
        Given a Centreon server
        And I am logged in
        When I change the configuration value of number of elements loaded in select
        Then the value is saved
