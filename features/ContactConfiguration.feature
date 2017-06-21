Feature: ContactConfiguration
    As a Centreon admin user
    I want to create a contact
    To configure it

    Background:
        Given I am logged in a Centreon server
        Given a contact is configured

    Scenario: Edit the name of a contact
        When I configure the name of a contact
        Then the name has changed on the contact page

    Scenario: Edit the alias of a contact
        When I configure the alias of a contact
        Then the alias has changed on the contact page

    Scenario: Edit the email of a contact
        When I configure the email of a contact
        Then the email has changed on the contact page

    Scenario: I make a contact become an admin
        When I make a contact be an admin
        Then the contact is now an admin

    Scenario: Edit the DN of a contact
        When I configure the DN of a contact
        Then the DN has changed
