Feature: Add a filter to display the services of disabled hosts
  As a Centreon admin
  I want to display the services of disabled hosts
  To improve my configuration

  Background:
    Given I am logged in a Centreon server

  Scenario: Add a checkbox to show the services of disabled hosts
    Given an host with configured services
    And the host is disabled
    When I access to the menu of services configuration
    And I activate the visibility filter of disabled hosts
    Then the services of disabled hosts are displayed