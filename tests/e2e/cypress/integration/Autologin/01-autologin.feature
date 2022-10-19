Feature: Autologin
  As a Centreon Web user
  I want to autologin automatically without password
  In order to access selected pages without going through the login process
  So the selected pages can be displayed permanently on a screen

Scenario: Enable autologin on the platform
  Given an Administrator is logged in the platform 
  When the administrator activates autologin on the platform
  Then any user of the plateform should be able to generate an autologin link 

Scenario: Generate autologin key
  Given an authenticated user and autologin configuration menus can be accessed
  When a user generates his autologin key
  Then the key is properly generated and displayed 

Scenario: Generate autologin link
  Given a User with autologin key generated
  When a User generates an autologin link  
  Then the autologin link is copied in the clipboard

Scenario: Connection using autologin
  Given a plateform with autologin enabled and a user with autologin key generated and a user with autologin link generated
  When the user opens the autologin link in a browser
  Then the page is accessed without manual login