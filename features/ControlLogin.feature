Feature: ControlLogin
    As a cybersecurity officer
    I want to be sure that without proper alias or password, nobody can access to Centreon Web
    In order to protect our IT infrastructure

    Background:
        Given I am logged in a Centreon server

    Scenario: Wrong alias
        When an existing user able to connect to Centreon Web
        And I type a wrong alias but a correct password
        Then I cannot access to Centreon

    Scenario: Bad password
        When I type a wrong password but a correct alias
        Then I cannot access to Centreon
