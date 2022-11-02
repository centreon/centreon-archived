Feature: Autologin
  As a Centreon Web user
  I want to autologin automatically without password
  In order to access selected pages without going through the login process
  So the selected pages can be displayed permanently on a screen

Scenario: Enable autologin on the platform
  Given an administrator is logged in the platform 
  When the administrator activates autologin on the platform
  Then any user of the platform should be able to generate an autologin link 

Scenario: Generate autologin key
  Given an authenticated user and the autologin configuration menu can be accessed
  When a user generates his autologin key
  Then the key is properly generated and displayed 

Scenario: Generate autologin link
  Given a user with an autologin key generated
  When a user generates an autologin link  
  Then the autologin link is copied in the clipboard

Scenario: Connection using autologin
  Given a platform with autologin enabled and a user with both autologin key and link generated
  When the user opens the autologin link in a browser
  Then the page is reached without manual login