Feature: AclAccessGroups
    As a Centreon administrator
    I want to administrate ACL access groups
    To give access to Centreon pages to users according their role in the company
	
    Background:
        Given I am logged in a Centreon server
        
    Scenario: Creating ACL access group with linked contacts
        When one contact group exists including two non admin contacts
        And the access group is saved with its properties
        Then all linked users have the access list group displayed in Centreon authentication tab

    Scenario: Creating ACL access group with linked contact group
        When I add a new access group with linked contact group
        And the access group is saved with its properties
        Then the Contact group has the access list group displayed in Relations informations

    Scenario: Modify ACL access group properties
        Given one existing ACL access group
        When I modify its properties
        Then all modified properties are updated

    Scenario: Duplicate ACL access group
        Given one existing ACL access group
        When I duplicate the access group
        Then a new access group appears with similar properties

    Scenario: Delete ACL access group
        Given one existing ACL access group
        When I delete the access group
        Then it does not exist anymore

    Scenario: Disable ACL access group
        Given one existing enabled ACL access group
        When I disable it
        Then its status is modified

    @Critical
    Scenario: Check the 'ACL group' search field with  a XSS vulnerability
        When I am on the ACL group list page
        And I put XSS script in the 'ACL group' search field
        Then the HTML in ACL group search field is not interpreted

    @Critical
    Scenario: Check XSS vulnerability in the ACL group list page
        When I add a ACL group
        Then the HTML is not interpreted in the list page

