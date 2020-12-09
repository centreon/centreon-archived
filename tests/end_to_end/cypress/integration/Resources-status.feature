Feature: Filter a list of resources
  As a user
  I want to apply filter(s) on a list of resources
  So that I can quickly view a specific group of these resources

  Background:
    Given a valid centreon user account
    And there is a page with a list of resources under monitoring
    And the user can access this page
    And there is a filters menu on this page
    And the resources contains a service named "Ping" in the list

  Scenario: User first access to the page
    When the user accesses the page for the first time
    Then a default filter is applied

  Scenario: User can choose from predefined filters
    When the user clicks on the predefined filters selection
    Then the predefined filters should be listed

  Scenario: User resets applied filters
    Given filters already applied
    When user clicks on Clear button
    Then all selected filters should be reset to their default value
    And search filter should be emptied

  Scenario: User applies filter(s)
    Given the user has selected filters
    And the user has input a search pattern
    When the user clicks on the SEARCH button
    Then only resources matching the user selected filters should be shown in the result

  Scenario: Selected filters are retained when leaving the page
    Given a set of filters applied to the resources list
    When the user leaves the page
    Then the set of filters should be retained on his next visit