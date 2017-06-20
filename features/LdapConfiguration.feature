Feature: LDAP_configuration
    As a company administrator
    I want to configure LDAP
    In order to users to connect easily	to Centreon web
	
    Background:
        Given I am logged in a Centreon server

    Scenario: Creating LDAP configuration
        When I add a new LDAP configuration
        Then the LDAP configuration is saved with its properties

    #Scenario: Duplicate LDAP configuration
        #Given an existing LDAP configuration
        #When I duplicate the LDAP configuration
        #Then name is automatically incremented
	#And other properties are the same than in the model

    #Scenario: Modify duplicated LDAP configuration
        #When I modify some properties of an existing LDAP configuration
        #Then all changes are saved

    #Scenario: Delete LDAP configuration
        #When I have deleted one existing LDAP configuration
        #Then this configuration has disappeared from the LDAP configuration list