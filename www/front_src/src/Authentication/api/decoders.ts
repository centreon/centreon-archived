import { JsonDecoder } from 'ts.data.json';

import { PasswordExpiration, PasswordSecurityPolicy } from '../Local/models';
import {
  AuthConditions,
  Endpoint,
  EndpointType,
  GroupsMapping,
  GroupsRelation,
  NamedEntity,
  OpenidConfiguration,
  RolesMapping,
  RolesRelation,
} from '../Openid/models';
import { WebSSOConfiguration } from '../WebSSO/models';

const passwordExpirationDecoder = JsonDecoder.object<PasswordExpiration>(
  {
    excludedUsers: JsonDecoder.array(JsonDecoder.string, 'excludedUsers'),
    expirationDelay: JsonDecoder.nullable(JsonDecoder.number),
  },
  'PasswordExpiration',
  {
    excludedUsers: 'excluded_users',
    expirationDelay: 'expiration_delay',
  },
);

export const securityPolicyDecoder = JsonDecoder.object<PasswordSecurityPolicy>(
  {
    attempts: JsonDecoder.nullable(JsonDecoder.number),
    blockingDuration: JsonDecoder.nullable(JsonDecoder.number),
    canReusePasswords: JsonDecoder.boolean,
    delayBeforeNewPassword: JsonDecoder.nullable(JsonDecoder.number),
    hasLowerCase: JsonDecoder.boolean,
    hasNumber: JsonDecoder.boolean,
    hasSpecialCharacter: JsonDecoder.boolean,
    hasUpperCase: JsonDecoder.boolean,
    passwordExpiration: passwordExpirationDecoder,
    passwordMinLength: JsonDecoder.number,
  },
  'PasswordSecurityPolicy',
  {
    blockingDuration: 'blocking_duration',
    canReusePasswords: 'can_reuse_passwords',
    delayBeforeNewPassword: 'delay_before_new_password',
    hasLowerCase: 'has_lowercase',
    hasNumber: 'has_number',
    hasSpecialCharacter: 'has_special_character',
    hasUpperCase: 'has_uppercase',
    passwordExpiration: 'password_expiration',
    passwordMinLength: 'password_min_length',
  },
);

const getNamedEntityDecoder = (
  title: string,
): JsonDecoder.Decoder<NamedEntity> =>
  JsonDecoder.object<NamedEntity>(
    {
      id: JsonDecoder.number,
      name: JsonDecoder.string,
    },
    title,
  );

const endpointDecoder = JsonDecoder.object<Endpoint>(
  {
    customEndpoint: JsonDecoder.nullable(JsonDecoder.string),
    type: JsonDecoder.enumeration(EndpointType, 'type'),
  },
  'Endpoint',
  {
    customEndpoint: 'custom_endpoint',
  },
);

const rolesRelation = JsonDecoder.object<RolesRelation>(
  {
    accessGroup: getNamedEntityDecoder('Access group'),
    claimValue: JsonDecoder.string,
    priority: JsonDecoder.number,
  },
  'Role Relation',
  {
    accessGroup: 'access_group',
    claimValue: 'claim_value',
    priority: 'priority',
  },
);

const groupsRelationDecoder = JsonDecoder.object<GroupsRelation>(
  {
    contactGroup: getNamedEntityDecoder('Contact group'),
    groupValue: JsonDecoder.string,
  },
  'Group Relation',
  {
    contactGroup: 'contact_group',
    groupValue: 'group_value',
  },
);

const authConditions = JsonDecoder.object<AuthConditions>(
  {
    attributePath: JsonDecoder.string,
    authorizedValues: JsonDecoder.array(
      JsonDecoder.string,
      'condition authorized value',
    ),
    blacklistClientAddresses: JsonDecoder.array(
      JsonDecoder.string,
      'blacklist client addresses',
    ),
    endpoint: endpointDecoder,
    isEnabled: JsonDecoder.boolean,
    trustedClientAddresses: JsonDecoder.array(
      JsonDecoder.string,
      'trusted client addresses',
    ),
  },
  'Authentication conditions',
  {
    attributePath: 'attribute_path',
    authorizedValues: 'authorized_values',
    blacklistClientAddresses: 'blacklist_client_addresses',
    isEnabled: 'is_enabled',
    trustedClientAddresses: 'trusted_client_addresses',
  },
);

const rolesMapping = JsonDecoder.object<RolesMapping>(
  {
    applyOnlyFirstRole: JsonDecoder.boolean,
    attributePath: JsonDecoder.string,
    endpoint: endpointDecoder,
    isEnabled: JsonDecoder.boolean,
    relations: JsonDecoder.array(rolesRelation, 'Roles relation'),
  },
  'Roles mapping',
  {
    applyOnlyFirstRole: 'apply_only_first_role',
    attributePath: 'attribute_path',
    isEnabled: 'is_enabled',
  },
);

const groupsMappingDecoder = JsonDecoder.object<GroupsMapping>(
  {
    attributePath: JsonDecoder.string,
    endpoint: endpointDecoder,
    isEnabled: JsonDecoder.boolean,
    relations: JsonDecoder.array(groupsRelationDecoder, 'Groups relation'),
  },
  'Groups mapping',
  {
    attributePath: 'attribute_path',
    isEnabled: 'is_enabled',
  },
);

export const openidConfigurationDecoder =
  JsonDecoder.object<OpenidConfiguration>(
    {
      authenticationConditions: authConditions,
      authenticationType: JsonDecoder.nullable(JsonDecoder.string),
      authorizationEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      autoImport: JsonDecoder.boolean,

      baseUrl: JsonDecoder.nullable(JsonDecoder.string),
      clientId: JsonDecoder.nullable(JsonDecoder.string),
      clientSecret: JsonDecoder.nullable(JsonDecoder.string),
      connectionScopes: JsonDecoder.array(
        JsonDecoder.string,
        'connectionScopes',
      ),
      contactTemplate: JsonDecoder.nullable(
        getNamedEntityDecoder('Contact template'),
      ),
      emailBindAttribute: JsonDecoder.nullable(JsonDecoder.string),
      endSessionEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      fullnameBindAttribute: JsonDecoder.nullable(JsonDecoder.string),
      groupsMapping: groupsMappingDecoder,
      introspectionTokenEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      isActive: JsonDecoder.boolean,

      isForced: JsonDecoder.boolean,
      loginClaim: JsonDecoder.nullable(JsonDecoder.string),

      rolesMapping,

      tokenEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      userinfoEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      verifyPeer: JsonDecoder.boolean,
    },
    'Open ID Configuration',
    {
      authenticationConditions: 'authentication_conditions',
      authenticationType: 'authentication_type',
      authorizationEndpoint: 'authorization_endpoint',
      autoImport: 'auto_import',
      baseUrl: 'base_url',
      clientId: 'client_id',
      clientSecret: 'client_secret',
      connectionScopes: 'connection_scopes',
      contactTemplate: 'contact_template',
      emailBindAttribute: 'email_bind_attribute',
      endSessionEndpoint: 'endsession_endpoint',
      fullnameBindAttribute: 'fullname_bind_attribute',
      groupsMapping: 'groups_mapping',
      introspectionTokenEndpoint: 'introspection_token_endpoint',
      isActive: 'is_active',
      isForced: 'is_forced',
      loginClaim: 'login_claim',
      rolesMapping: 'roles_mapping',
      tokenEndpoint: 'token_endpoint',
      userinfoEndpoint: 'userinfo_endpoint',
      verifyPeer: 'verify_peer',
    },
  );

export const webSSOConfigurationDecoder =
  JsonDecoder.object<WebSSOConfiguration>(
    {
      blacklistClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'blacklist client addresses',
      ),
      isActive: JsonDecoder.boolean,
      isForced: JsonDecoder.boolean,
      loginHeaderAttribute: JsonDecoder.optional(
        JsonDecoder.nullable(JsonDecoder.string),
      ),
      patternMatchingLogin: JsonDecoder.optional(
        JsonDecoder.nullable(JsonDecoder.string),
      ),
      patternReplaceLogin: JsonDecoder.optional(
        JsonDecoder.nullable(JsonDecoder.string),
      ),
      trustedClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'trusted client addresses',
      ),
    },
    'Web SSO Configuration',
    {
      blacklistClientAddresses: 'blacklist_client_addresses',
      isActive: 'is_active',
      isForced: 'is_forced',
      loginHeaderAttribute: 'login_header_attribute',
      patternMatchingLogin: 'pattern_matching_login',
      patternReplaceLogin: 'pattern_replace_login',
      trustedClientAddresses: 'trusted_client_addresses',
    },
  );
