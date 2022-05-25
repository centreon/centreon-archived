import { JsonDecoder } from 'ts.data.json';

import { PasswordExpiration, PasswordSecurityPolicy } from '../Local/models';
import {
  Authorization,
  NamedEntity,
  OpenidConfiguration,
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

const authorization = JsonDecoder.object<Authorization>(
  {
    accessGroup: getNamedEntityDecoder('Access group'),
    name: JsonDecoder.string,
  },
  'Authorization',
  {
    accessGroup: 'access_group',
  },
);

export const openidConfigurationDecoder =
  JsonDecoder.object<OpenidConfiguration>(
    {
      aliasBindAttribute: JsonDecoder.nullable(JsonDecoder.string),
      authenticationType: JsonDecoder.nullable(JsonDecoder.string),
      authorizationClaim: JsonDecoder.array(
        authorization,
        'Authorization claim',
      ),
      authorizationEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      autoImport: JsonDecoder.boolean,
      baseUrl: JsonDecoder.nullable(JsonDecoder.string),
      blacklistClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'blacklist client addresses',
      ),
      clientId: JsonDecoder.nullable(JsonDecoder.string),
      clientSecret: JsonDecoder.nullable(JsonDecoder.string),
      connectionScopes: JsonDecoder.array(
        JsonDecoder.string,
        'connectionScopes',
      ),
      contactGroup: JsonDecoder.nullable(
        getNamedEntityDecoder('Contact group'),
      ),
      contactTemplate: JsonDecoder.nullable(
        getNamedEntityDecoder('Contact template'),
      ),
      emailBindAttribute: JsonDecoder.nullable(JsonDecoder.string),
      endSessionEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      fullnameBindAttribute: JsonDecoder.nullable(JsonDecoder.string),
      introspectionTokenEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      isActive: JsonDecoder.boolean,
      isForced: JsonDecoder.boolean,
      loginClaim: JsonDecoder.nullable(JsonDecoder.string),
      tokenEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      trustedClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'trusted client addresses',
      ),
      userinfoEndpoint: JsonDecoder.nullable(JsonDecoder.string),
      verifyPeer: JsonDecoder.boolean,
    },
    'Open ID Configuration',
    {
      aliasBindAttribute: 'alias_bind_attribute',
      authenticationType: 'authentication_type',
      authorizationClaim: 'authorization_claim',
      authorizationEndpoint: 'authorization_endpoint',
      autoImport: 'auto_import',
      baseUrl: 'base_url',
      blacklistClientAddresses: 'blacklist_client_addresses',
      clientId: 'client_id',
      clientSecret: 'client_secret',
      connectionScopes: 'connection_scopes',
      contactGroup: 'contact_group',
      contactTemplate: 'contact_template',
      emailBindAttribute: 'email_bind_attribute',
      endSessionEndpoint: 'endsession_endpoint',
      fullnameBindAttribute: 'fullname_bind_attribute',
      introspectionTokenEndpoint: 'introspection_token_endpoint',
      isActive: 'is_active',
      isForced: 'is_forced',
      loginClaim: 'login_claim',
      tokenEndpoint: 'token_endpoint',
      trustedClientAddresses: 'trusted_client_addresses',
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
