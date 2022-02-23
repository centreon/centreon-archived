import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyToAPI,
} from '../Local/models';

export const adaptPasswordSecurityPolicyFromAPI = (
  securityPolicy: PasswordSecurityPolicy,
): PasswordSecurityPolicy => {
  return {
    ...securityPolicy,
    blockingDuration: securityPolicy.blockingDuration
      ? securityPolicy.blockingDuration * 1000
      : null,
    delayBeforeNewPassword: securityPolicy.delayBeforeNewPassword
      ? securityPolicy.delayBeforeNewPassword * 1000
      : null,
    passwordExpiration: {
      ...securityPolicy.passwordExpiration,
      expirationDelay: securityPolicy.passwordExpiration.expirationDelay
        ? securityPolicy.passwordExpiration.expirationDelay * 1000
        : null,
    },
  };
};

export const adaptPasswordSecurityPolicyToAPI = ({
  passwordMinLength,
  delayBeforeNewPassword,
  canReusePasswords,
  passwordExpiration,
  hasSpecialCharacter,
  hasNumber,
  hasLowerCase,
  hasUpperCase,
  attempts,
  blockingDuration,
}: PasswordSecurityPolicy): PasswordSecurityPolicyToAPI => {
  return {
    password_security_policy: {
      attempts,
      blocking_duration: blockingDuration ? blockingDuration / 1000 : null,
      can_reuse_passwords: canReusePasswords,
      delay_before_new_password: delayBeforeNewPassword
        ? delayBeforeNewPassword / 1000
        : null,
      has_lowercase: hasLowerCase,
      has_number: hasNumber,
      has_special_character: hasSpecialCharacter,
      has_uppercase: hasUpperCase,
      password_expiration: {
        excluded_users: passwordExpiration.excludedUsers,
        expiration_delay: passwordExpiration.expirationDelay
          ? passwordExpiration.expirationDelay / 1000
          : null,
      },
      password_min_length: passwordMinLength,
    },
  };
};
