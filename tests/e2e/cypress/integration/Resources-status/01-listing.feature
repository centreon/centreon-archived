Feature: Filter a list of Resources
  As a user
  I want to apply filter(s) on a list of Resources
  So that I can quickly view a specific group of these Resources

  Background:
    Given There are available resources

  Scenario: I first access to the page
    When I filter on unhandled problems
    Then Only non-ok resources are displayed

  Scenario: I can filter Resources
    When I put in some criterias 
    Then only the Resources matching the selected criterias are displayed in the result

  Scenario: I can select filters
    Given a saved custom filter
    When I select the custom filter
    Then only Resources matching the selected filter are displayed in the result
