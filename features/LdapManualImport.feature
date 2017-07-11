Feature: LDAPManualImport
    As a company administrator
    I want to import manually users
    In order to filter the ones who can access to Centreon Web
	
    Background:
        Given I am logged in a Centreon server with a configured ldap
        And a LDAP configuration with Users auto import disabled has been created

    Scenario: Search and import one user whose alias contains an accent
        Given I search a specific user whose alias contains a special character such as an accent
        And the LDAP search result displays the expected alias
        When I import the user
        Then the user is created
		
    Scenario: LDAP manually imported user can authenticate to Centreon Web
        Given one alias with an accent has been manually imported
        When this user logins to Centreon Web
        Then he's logged by default on Home page
