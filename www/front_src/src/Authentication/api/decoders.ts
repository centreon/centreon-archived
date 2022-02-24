import { JsonDecoder } from 'ts.data.json';

import { PasswordExpiration, PasswordSecurityPolicy } from '../Local/models';
import { OpenidConfiguration } from '../Openid/models';

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
      authenticationType: JsonDecoder.string,
      authorizationEndpoint: JsonDecoder.string,
      baseUrl: JsonDecoder.string,
      blacklistClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'blacklist client addresses',
      ),
      clientId: JsonDecoder.string,
      clientSecret: JsonDecoder.string,
      connectionScopes: JsonDecoder.array(
        JsonDecoder.string,
        'connectionScopes',
      ),
      endSessionEndpoint: JsonDecoder.string,
      introspectionTokenEndpoint: JsonDecoder.string,
      isActive: JsonDecoder.boolean,
      isForced: JsonDecoder.boolean,
      loginClaim: JsonDecoder.string,
      tokenEndpoint: JsonDecoder.string,
      trustedClientAddresses: JsonDecoder.array(
        JsonDecoder.string,
        'trusted client addresses',
      ),
      userinfoEndpoint: JsonDecoder.string,
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
