Feature: Check if default configuration is empty after a fresh install
  As a Centreon admin
  I want to have no default configuration
  To create my configuration or use PluginPack configuration

  Scenario: List host template
    Given I am logged in a freshly installed Centreon server
    When I list the "host template"
    Then no item is display

  Scenario: List service template
    Given I am logged in a freshly installed Centreon server
    When I list the "service template"
    Then no item is display

  Scenario: Command template
    Given I am logged in a freshly installed Centreon server
    When I list the "command"
    Then no item is display
