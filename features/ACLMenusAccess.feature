Feature: ACL Menus Access administration
    As a Centreon administrator
    I want to administrate Menus Access
    To give access to Centreon pages to users according their role in the company
	
    Background:
        Given I am logged in a Centreon server
        And three ACL access groups have been created
                     
    Scenario: Creating ACL Menu Access linked to several access groups
        When I add a new menu access linked with two groups
        Then the menu access is saved with its properties
        And only chosen linked access groups display the new menu access in Authorized information tab
		
    Scenario: Remove one access group from Menu access 
        Given one existing ACL Menu access linked with two access groups
        When I remove one access group
        Then link between access group and Menu access must be broken

    Scenario: Duplicate one existing Menu access
        Given one existing Menu access
        When I duplicate the Menu access
        Then a new Menu access is created with identical properties except the name

    Scenario: Disable one existing Menu access
        Given one existing enabled Menu access
        When I disable it
        Then its status is modified

    Scenario: Delete one existing Menu access
        Given one existing Menu access
        When I delete the Menu access
        Then the menu access record is not visible anymore in Menus Access page
        And the link with access groups is broken
