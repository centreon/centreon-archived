Feature: List Access Group API
  In order to use List Access Groups API
  As a logged user
  I need to be able to list Access Groups

  Background:
    Given a running instance of Centreon Web API

  Scenario: List Access Groups as an admin user
    Given I am logged in
    And the endpoints are described in Centreon Web API documentation (version: 22.10)

    When I send a GET request to '/api/latest/configuration/access-groups'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "result": [
          {
            "id": 1,
            "name": "ALL",
            "alias": "ALL",
            "has_changed": false,
            "is_activated": true
          }
        ],
        "meta": {
          "page": 1,
          "limit": 10,
          "search": {},
          "sort_by": {},
          "total": 1
        }
      }
    """

  Scenario: List Access Groups as a non-admin user with no rights to Reach API
    Given the following CLAPI import data:
    """
      CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
      CONTACT;setparam;kev;reach_api;0
    """
    And I am logged in with "kev"/"Centreon@2022"

    When I send a GET request to '/api/latest/configuration/access-groups'
    Then the response code should be "403"

  Scenario: List Access Groups as a non-admin user with the rights to Reach API and Access Groups
    Given the following CLAPI import data:
    """
      CONTACT;ADD;kev;kev;kev@localhost;$2y$10$M61cjm4zlrlrEUtv081FJugTqk6MFuK5bwV8yDxZb6edXgI7n7Gl2;0;1;en_US.UTF-8;local
      CONTACT;setparam;kev;hostnotifopt;n
      CONTACT;setparam;kev;servicenotifopt;n
      CONTACT;setparam;kev;contact_js_effects;0
      CONTACT;setparam;kev;contact_theme;light
      CONTACT;setparam;kev;timezone;0
      CONTACT;setparam;kev;reach_api;1
      CONTACT;setparam;kev;reach_api_rt;0
      CONTACT;setparam;kev;contact_enable_notifications;0
      CONTACT;setparam;kev;contact_type_msg;txt
      CONTACT;setparam;kev;contact_activate;1
      CONTACT;setparam;kev;show_deprecated_pages;0
      CONTACT;setparam;kev;contact_ldap_last_sync;0
      CONTACT;setparam;kev;contact_ldap_required_sync;0
    """
    And I am logged in with "kev"/"Centreon@2022"

    When I send a GET request to '/api/latest/configuration/access-groups'
    Then the response code should be "200"

  # Scenario: List Access Groups as a non-admin user with the rights to Reach API and no rights to Access