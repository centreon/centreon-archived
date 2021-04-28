Feature: Filter a list of Resources
  As a user
  I want to apply filter(s) on a list of Resources
  So that I can quickly view a specific group of these Resources

  Background:
    Given There are available resources

  Scenario: I first access to the page
    Then the unhandled problems are displayed

  Scenario: I can filter Resources
    When I put in some criterias 
    Then only Resources matching I selected criterias should be displayed in the result

  Scenario: I can select filters
    Given a saved custom filter
    When I select the custom filter
    Then only Resources matching I selected filter should be displayed in the result