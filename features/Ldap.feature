#features/Ldap.feature

Feature: LDAP
    As a company administrator
    I want my users to access Centreon using LDAP credentials
    So that I can easily manage the credentials

    Background:
        Given I am logged in a Centreon server with a configured ldap

    Scenario: User cannot change DN
        Given a ldap user has been imported
        When I am on the ldap contact page with a non admin user
        Then I cannot update the contact dn
        And I cannot update the contact password
