Feature: MetaserviceApi
  As a Web developer
  I wish to create a table from services “classical”, “meta services” or both
  To integrate this one in a list of selection

  Background:
    Given I am logged in a Centreon server
    And I have a meta service

  Scenario: param equal all
    When a call to API configuration services with s equal all is defined
    Then the table understands the services and the meta services

  Scenario: param equal s
    When a call to API configuration services with s equal s is defined
    Then the table understands only the services

  Scenario: param equal m
    When a call to API configuration services with s equal m is defined
    Then the table understands only the meta services
