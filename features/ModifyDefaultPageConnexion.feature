Feature: Modify_Default_Page_Connexion
    As a Centreon Web user 
    I want to change the default connexion page
    To access directly to the one I have chosen
        
    Background: 
        Given I am logged in a Centreon server
        And I have access to all menus
        
    Scenario: Changing default page connexion for an admin user
        Given I have admin rights
        And I have replaced the default page connexion with Administration > Parameters
        When I log back to Centreon
        Then the active page is Administration > Parameters

    Scenario: Changing default page connexion for a non admin user
        Given I don't have admin rights
        And I have replaced the default page connexion with Monitoring > Status Details > Hosts
        When I log back to Centreon
        Then the active page is Monitoring > Status Details > Hosts
