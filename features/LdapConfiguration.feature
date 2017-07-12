Feature: LdapConfiguration
    As a company administrator
    I want to configure LDAP
    In order to administrate easily all logins to applications used in company
    
    Background: 
        Given I am logged in a Centreon server

    Scenario: Creating LDAP configuration
        When I add a new LDAP configuration
        Then the LDAP configuration is saved with its properties
        
    Scenario: Modify LDAP configuration
        When I modify some properties of an existing LDAP configuration
        Then all changes are saved

    Scenario: Delete LDAP configuration
        When I have deleted one existing LDAP configuration
        Then this configuration has disappeared from the LDAP configuration list
