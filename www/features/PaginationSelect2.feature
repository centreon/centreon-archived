Feature:
    As a Centreon admin
    I change the number of elements loaded in select
    To optimizr the data presentation

    Background:
        Given I am logged in
        
    Scenario: Change the value of number of elements loaded in select
        Given a Centreon platform
        When I change the configuration value of number of elements loaded in select
        Then the value is saved