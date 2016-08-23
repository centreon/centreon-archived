Feature: Use enter in select2
  As a Centreon user
  I want to use the select2 with my keyboard
  To facilitate the user experience

  Background:
    Given I am logged in a Centreon server

  Scenario: Use enter to validate select2
    Given I search on a select2
    When research give results
    Then I can use ENTER key to validate my choice

