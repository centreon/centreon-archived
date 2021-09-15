Feature: Top Counter Profile Menu
  As a Centreon User
  I want to use the top counter profile menu
  To manage my profile

  Scenario: Go to my profile edit form
    When I click to edit profile link
    Then I see my profile edit form

  Scenario: Logout
    When I click to logout link
    Then I see the login page