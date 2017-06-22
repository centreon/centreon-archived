Feature: ContactConfiguration
    As a Centreon admin user
    I want to create a contact
    To configure it

    Background:
        Given I am logged in a Centreon server
        Given a contact is configured

    Scenario: Edit the name of a contact
        When I configure the name of a contact
        And I configure the alias of a contact
        And I configure the email of a contact
        And I configure the access of a contact
        And I make a contact be an admin
        And I configure the status of a contact
        And I configure the DN of a contact
        And I configure the host_notif_period
        And I configure the service_notif_period
        Then the name has changed on the contact page
        And the alias has changed on the contact page
        And the email has changed on the contact page
        And the access has changed on the contact page
        And the contact is now an admin
        And the status has changed on the contact page
        And the DN has changed
        And the host_notif_period has changed
        And the service_notif_period has changed
