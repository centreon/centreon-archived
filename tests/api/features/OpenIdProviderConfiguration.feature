Feature: OpenId Provider Configuration API
  In order to use OpenId Provider Configuration API
  As a logged user
  I need to be able to update and retreive OpenId Provider Configuration information

  Background:
    Given a running instance of Centreon Web API

  Scenario: Update and retrieve OpenId Provider Configuration
    Given I am logged in
    And the endpoints are described in Centreon Web API documentation (version: 22.10)

    # Valid PUT request
    When I send a PUT request to '/api/latest/administration/authentication/providers/openid' with body:
    """
      {
        "is_active": true,
        "is_forced": false,
        "base_url": "https://localhost:8080",
        "authorization_endpoint": "/authorize",
        "token_endpoint": "/token",
        "introspection_token_endpoint": null,
        "userinfo_endpoint": "/userinfo",
        "endsession_endpoint": "/logout",
        "connection_scopes": ["openid", "offline_access"],
        "login_claim": "given_name",
        "client_id": "user2",
        "client_secret": "Centreon!2021",
        "authentication_type": "client_secret_post",
        "verify_peer": false,
        "auto_import": true,
        "contact_template": {
          "id": 19,
          "name": "contact_template"
        },
        "email_bind_attribute": "email",
        "fullname_bind_attribute": "given_name",
        "authentication_conditions": {
            "is_enabled": true,
            "attribute_path": "users.roles.info.status",
            "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
            },
            "authorized_values": ["status2"],
            "trusted_client_addresses": [],
            "blacklist_client_addresses": []
        },
        "roles_mapping": {
          "is_enabled": false,
          "attribute_path": "users.roles.info.status",
          "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
          },
          "apply_only_first_role": false,
          "relations": [{
            "claim_value": "status1",
            "access_group_id": 1
          }]
        },
        "groups_mapping": {
          "is_enabled": false,
          "attribute_path": "users.roles.info.status",
          "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
          },
          "relations": [
          ]
        }
      }
    """
    Then the response code should be "204"

    # Valid GET request
    When I send a GET request to '/api/latest/administration/authentication/providers/openid'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "is_active": true,
        "is_forced": false,
        "base_url": "https://localhost:8080",
        "authorization_endpoint": "/authorize",
        "token_endpoint": "/token",
        "introspection_token_endpoint": null,
        "userinfo_endpoint": "/userinfo",
        "endsession_endpoint": "/logout",
        "connection_scopes": ["openid", "offline_access"],
        "login_claim": "given_name",
        "client_id": "user2",
        "client_secret": "Centreon!2021",
        "authentication_type": "client_secret_post",
        "verify_peer": false,
        "auto_import": true,
        "contact_template": {
          "id": 19,
          "name": "contact_template"
        },
        "email_bind_attribute": "email",
        "fullname_bind_attribute": "given_name",
        "authentication_conditions": {
            "is_enabled": true,
            "attribute_path": "users.roles.info.status",
            "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
            },
            "authorized_values": ["status2"],
            "trusted_client_addresses": [],
            "blacklist_client_addresses": []
        },
        "roles_mapping": {
          "is_enabled": false,
          "attribute_path": "users.roles.info.status",
          "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
          },
          "apply_only_first_role": false,
          "relations": [{
            "claim_value": "status1",
            "access_group": {
              "id": 1,
              "name": "ALL"
            }
          }]
        },
        "groups_mapping": {
          "is_enabled": false,
          "attribute_path": "users.roles.info.status",
          "endpoint": {
              "type": "custom_endpoint",
              "custom_endpoint": "/my/custom/endpoint"
          },
          "relations": [
          ]
        }
      }
    """
