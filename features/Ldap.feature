#features/Ldap.feature

Feature: Proxy usage
    As a Centreon IMP client
    I want to use a proxy on my platform
    To access internet

    Background:
        Given I am logged in a Centreon server with a configured ldap

    Scenario: IMP Proxy usage
        And a ldap user has been imported
        When I am on the ldap contact page with a non admin user
        Then I cannot update the contact dn
        And I cannot update the contact password