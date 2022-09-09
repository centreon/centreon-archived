Feature: AliasContactModification
    As a Centreon admin user
    I want to modify an existing non admin contact alias including a special character
    Modified contact is saved
    Modified contact can log in Centreon Web

   Background:
       Given I am logged in a Centreon server
       And one non admin contact has been created

   Scenario: Modify contact alias by adding an accent or a special character
       When I have changed the contact alias
       Then the new record is displayed in the users list with the new alias value

   Scenario: Check modified contact still able to log in Centreon Web
       Given the contact alias contains an accent
       When I fill login field and Password
       Then the contact is logged to Centreon Web
