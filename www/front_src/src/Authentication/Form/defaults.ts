import { adaptSecurityPolicyToAPI } from '../api/adapters';
import {
  SecurityPolicy,
  SecurityPolicyFromAPI,
  SecurityPolicyToAPI,
} from '../models';
import {
  fifteenMinutes,
  oneDay,
  oneHour,
  sevenDays,
  twelveMonths,
} from '../timestamps';

export const defaultSecurityPolicy: SecurityPolicyFromAPI = {
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
      expirationDelay: sevenDays,
    },
    passwordMinLength: 12,
  },
};

export const defaultSecurityPolicyWithNullValues: SecurityPolicyFromAPI = {
  password_security_policy: {
    attempts: null,
    blockingDuration: null,
    canReusePasswords: false,
    delayBeforeNewPassword: null,
    hasLowerCase: false,
    hasNumber: false,
    hasSpecialCharacter: false,
    hasUpperCase: false,
    passwordExpiration: {
      excludedUsers: [],
      expirationDelay: null,
    },
    passwordMinLength: 12,
  },
};

export const defaultSecurityPolicyAPI: SecurityPolicyToAPI =
  adaptSecurityPolicyToAPI(defaultSecurityPolicy.password_security_policy);

export const retrievedSecurityPolicyAPI: SecurityPolicyToAPI =
  adaptSecurityPolicyToAPI({
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
      expirationDelay: sevenDays,
    },
    passwordMinLength: 42,
  });

export const securityPolicyWithInvalidPasswordExpiration: SecurityPolicy = {
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
    expirationDelay: twelveMonths + oneDay,
  },
  passwordMinLength: 12,
};

export const securityPolicyWithInvalidDelayBeforeNewPassword: SecurityPolicy = {
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
    expirationDelay: sevenDays,
  },
  passwordMinLength: 12,
};

export const securityPolicyWithInvalidBlockingDuration: SecurityPolicy = {
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
    expirationDelay: sevenDays,
  },
  passwordMinLength: 12,
};
