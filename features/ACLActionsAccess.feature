Feature: ACL Actions Access
    As a Centreon administrator
    I want to administrate Actions Access
    To restrict users actions on Centreon objects
	
    Background:
        Given I am logged in a Centreon server
        And one ACL access group including a non admin user exists
        And one ACL access group linked to a contact group including an admin user exists

    Scenario: Creating Actions Access linked to one non admin access groups and to one admin access group
        When I add a new action access linked with the access groups
        Then the action access record is saved with its properties
        And all linked access group display the new actions access in authorized information tab

    Scenario: Creating action access by selecting authorized actions one by one
        When I select one by one all action to authorize them in a action access record I create
        Then all radio-buttons have to be checked

    Scenario: Creating actions access by selecting authorized actions by lots
        When I check button-radio for a lot of actions
        Then all buttons-radio of the authorized actions lot are checked
        
    Scenario: Remove one access group from Actions access 
        Given one existing action access
        When I remove the access group
        Then link between access group and action access must be broken

    Scenario: Duplicate one existing Actions access record
        Given one existing action access
        When I duplicate the action access
        Then a new action access record is created with identical properties except the name
       
    Scenario: Modify one existing Actions access record
        Given one existing action access
        When I modify some properties such as name, description, comments, status or authorized actions 
        Then the modifications are saved
        
    Scenario: Delete one existing Actions access record
        Given one existing action access
        When I delete the action access
        Then the action access record is not visible anymore in action access page
        Then the links with the acl groups are broken
