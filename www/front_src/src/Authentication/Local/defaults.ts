import { adaptPasswordSecurityPolicyToAPI } from '../api/adapters';

import {
  PasswordSecurityPolicyFromAPI,
  PasswordSecurityPolicyToAPI
} from './models';
import {
  fifteenMinutes,
  oneDay,
  oneHour,
  sevenDays,
  twelveMonths
} from './timestamps';

export const defaultPasswordSecurityPolicy: PasswordSecurityPolicyFromAPI = {
  password_security_policy: {
    attempts: 5,
    blockingDuration: fifteenMinutes,
    canReusePasswords: false,
    delayBeforeNewPassword: oneHour,
    hasLowerCase: true,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: true,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: sevenDays
    },
    passwordMinLength: 12
  }
};

export const defaultPasswordSecurityPolicyWithNullValues: PasswordSecurityPolicyToAPI =
  {
    password_security_policy: {
      attempts: null,
      blocking_duration: null,
      can_reuse_passwords: false,
      delay_before_new_password: null,
      has_lowercase: false,
      has_number: false,
      has_special_character: false,
      has_uppercase: false,
      password_expiration: {
        excluded_users: [],
        expiration_delay: null
      },
      password_min_length: 12
    }
  };

export const defaultPasswordSecurityPolicyAPI: PasswordSecurityPolicyToAPI =
  adaptPasswordSecurityPolicyToAPI(
    defaultPasswordSecurityPolicy.password_security_policy
  );

export const retrievedPasswordSecurityPolicyAPI: PasswordSecurityPolicyToAPI =
  adaptPasswordSecurityPolicyToAPI({
    attempts: 8,
    blockingDuration: fifteenMinutes,
    canReusePasswords: false,
    delayBeforeNewPassword: oneHour,
    hasLowerCase: false,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: false,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: sevenDays
    },
    passwordMinLength: 42
  });

export const securityPolicyWithInvalidPasswordExpiration: PasswordSecurityPolicyToAPI =
  adaptPasswordSecurityPolicyToAPI({
    attempts: 5,
    blockingDuration: fifteenMinutes,
    canReusePasswords: false,
    delayBeforeNewPassword: oneHour,
    hasLowerCase: true,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: true,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: twelveMonths + oneDay
    },
    passwordMinLength: 12
  });

export const securityPolicyWithInvalidDelayBeforeNewPassword: PasswordSecurityPolicyToAPI =
  adaptPasswordSecurityPolicyToAPI({
    attempts: 5,
    blockingDuration: fifteenMinutes,
    canReusePasswords: false,
    delayBeforeNewPassword: sevenDays + oneDay,
    hasLowerCase: true,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: true,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: sevenDays
    },
    passwordMinLength: 12
  });

export const securityPolicyWithInvalidBlockingDuration: PasswordSecurityPolicyToAPI =
  adaptPasswordSecurityPolicyToAPI({
    attempts: 5,
    blockingDuration: sevenDays + oneHour,
    canReusePasswords: false,
    delayBeforeNewPassword: oneHour,
    hasLowerCase: true,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: true,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: sevenDays
    },
    passwordMinLength: 12
  });
