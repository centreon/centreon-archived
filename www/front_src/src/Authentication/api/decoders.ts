import { JsonDecoder } from 'ts.data.json';

import { PasswordExpiration, PasswordSecurityPolicy } from '../Local/models';
import { OpenidConfiguration } from '../Openid/models';
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

export const openidConfigurationDecoder =
  JsonDecoder.object<OpenidConfiguration>(
    {
      authenticationType: JsonDecoder.nullable(JsonDecoder.string),
      authorizationEndpoint: JsonDecoder.nullable(JsonDecoder.string),
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
      endSessionEndpoint: JsonDecoder.nullable(JsonDecoder.string),
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
      authenticationType: 'authentication_type',
      authorizationEndpoint: 'authorization_endpoint',
      baseUrl: 'base_url',
      blacklistClientAddresses: 'blacklist_client_addresses',
      clientId: 'client_id',
      clientSecret: 'client_secret',
      connectionScopes: 'connection_scopes',
      endSessionEndpoint: 'endsession_endpoint',
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
