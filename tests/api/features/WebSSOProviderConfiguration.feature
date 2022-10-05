Feature: WebSSO Provider Configuration API
  In order to use WebSSO Provider Configuration API
  As a logged user
  I need to be able to update and retreive OpenId Provider Configuration information

  Background:
    Given a running instance of Centreon Web API

  Scenario: Update and retrieve WebSSO Provider Configuration
    Given I am logged in
    And the endpoints are described in Centreon Web API documentation (version: 22.10)

    # Valid PUT request
    When I send a PUT request to '/api/latest/administration/authentication/providers/web-sso' with body:
    """
      {
        "is_active": true,
        "is_forced": false,
        "trusted_client_addresses": ["127.0.0.2"],
        "blacklist_client_addresses": ["127.0.0.3"],
        "login_header_attribute": "REMOTE_USER",
        "pattern_matching_login": null,
        "pattern_replace_login": null
      }
    """
    Then the response code should be "204"

    # Valid Get request
    When I send a GET request to '/api/latest/administration/authentication/providers/web-sso'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "is_active": true,
        "is_forced": false,
        "trusted_client_addresses": ["127.0.0.2"],
        "blacklist_client_addresses": ["127.0.0.3"],
        "login_header_attribute": "REMOTE_USER",
        "pattern_matching_login": null,
        "pattern_replace_login": null
      }
    """

  Scenario: Update WebSSO Provider Configuration with invalid information
    Given I am logged in

    # Invalid PUT request: required property missing
    When I send a PUT request to '/api/latest/administration/authentication/providers/web-sso' with body:
    """
      {
        "is_active": true,
        "trusted_client_addresses": ["127.0.0.2"],
        "blacklist_client_addresses": ["127.0.0.3"],
        "login_header_attribute": "REMOTE_USER",
        "pattern_matching_login": null,
        "pattern_replace_login": null
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
    {
      "code": 500,
      "message": "[is_forced] The property is_forced is required\n"
    }
    """

    # Invalid PUT request: invalid value type passed to a property
    When I send a PUT request to '/api/latest/administration/authentication/providers/web-sso' with body:
    """
      {
        "is_active": true,
        "is_forced": false,
        "trusted_client_addresses": ["127.0.0.2"],
        "blacklist_client_addresses": ["127.0.0.3"],
        "login_header_attribute": 0,
        "pattern_matching_login": null,
        "pattern_replace_login": null
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
    {
      "code": 500,
      "message": "[login_header_attribute] Integer value found, but a string or a null is required\n"
    }
    """

  # Invalid PUT request: invalid value passed to "blacklist_client_addresses"
  When I send a PUT request to '/api/latest/administration/authentication/providers/web-sso' with body:
  """
      {
        "is_active": true,
        "is_forced": false,
        "trusted_client_addresses": ["127.0.0.2"],
        "blacklist_client_addresses": [".@"],
        "login_header_attribute": "REMOTE_USER",
        "pattern_matching_login": null,
        "pattern_replace_login": null
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[WebSSOConfiguration::blacklistClientAddresses] The value '.@' was expected to be a valid ip address"
      }
    """
