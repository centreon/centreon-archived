import { JsonDecoder } from 'ts.data.json';

import { PasswordExpiration, SecurityPolicy } from '../models';

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

export const securityPolicyDecoder = JsonDecoder.object<SecurityPolicy>(
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
  'SecurityPolicy',
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
