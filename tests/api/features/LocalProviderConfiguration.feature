Feature: Local Provider Configuration API
  In order to use Local Provider Configuration API
  As a logged user
  I need to be able to update and retreive Local Provider Configuration information

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Update and retrieve Local Provider Configuration information
    Given I am logged in
    When I send a PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3000
        }
      }
    """
    Then the response code should be "204"

    When I send a GET request to '/administration/authentication/providers/local'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": true,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 0,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3000
        }
      }
    """