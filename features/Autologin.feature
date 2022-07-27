Feature: Autologin
    As a Centreon Web user
    I want to autologin automatically without password
    In order to access selected pages without going through the login process
    So the selected pages can be displayed permanently on a screen

    Background:
        Given an authenticated user
        And autologin configuration menus can be accessed

    Scenario: Enable autologin on the platfrom
        Given an Administrator is logged in the plateform 
        When the administrator activates autologin on the plateform
        Then any user of the plateform should be able to generate an autologin link 
    
    Scenario: Generate autologin key
        Given
        When a user generate his autologin key
        Then the key is properly generated and displayed 

    Scenario: Generate autologin link
        Given a User with autologin key generated
        When a User generates an autologin link  
        Then the autologin link is copied in the clipboard

    Scenario: Connection using autologin
        Given a plateform with autologin enabled
        And a user with autologin key generated
        And a user with autologin link generated
        When the user opens the autologin link in a browser
        Then the page is accessed without manual login