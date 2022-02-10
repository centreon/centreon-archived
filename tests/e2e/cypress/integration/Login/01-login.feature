Feature: Login

Scenario: Logging in
  When I enter my credentials on the login page
  Then I am redirected to the default page

Scenario: Logging out
  Given I am logged in
  When I click on the logout action
  Then I am logged out and redirected to the login page
