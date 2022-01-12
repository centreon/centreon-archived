Feature: List Resources
  As a user
  I want to list the available Resources and filter them
  So that I can handle associated problems quickly and efficiently 

  Scenario: Accessing the page for the first time
    Then the unhandled problems filter is selected
    And only non-ok resources are displayed

  Scenario: Filtering Resources through criterias
    When I put in some criterias 
    Then only the Resources matching the selected criterias are displayed in the result

  Scenario: Selecting filters
    Given a saved custom filter
    When I select the custom filter
    Then only Resources matching the selected filter are displayed in the result
