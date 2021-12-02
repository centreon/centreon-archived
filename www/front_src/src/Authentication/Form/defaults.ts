import { adaptSecurityPolicyToAPI } from '../api/adapters';
import { SecurityPolicy, SecurityPolicyAPI } from '../models';
import { fiveTeenMinutes, oneHour, sevenDays } from '../timestamps';

export const defaultSecurityPolicy: SecurityPolicy = {
  attempts: 5,
  blockingDuration: fiveTeenMinutes,
  canReusePasswords: false,
  delayBeforeNewPassword: oneHour,
  hasLowerCase: true,
  hasNumber: true,
  hasSpecialCharacter: true,
  hasUpperCase: true,
  passwordExpiration: sevenDays,
  passwordMinLength: 12,
};

export const defaultSecurityPolicyWithNullValues: SecurityPolicy = {
  attempts: null,
  blockingDuration: null,
  canReusePasswords: false,
  delayBeforeNewPassword: null,
  hasLowerCase: false,
  hasNumber: false,
  hasSpecialCharacter: false,
  hasUpperCase: false,
  passwordExpiration: null,
  passwordMinLength: 12,
};

export const defaultSecurityPolicyAPI: SecurityPolicyAPI =
  adaptSecurityPolicyToAPI(defaultSecurityPolicy);

export const retrievedSecurityPolicyAPI: SecurityPolicyAPI =
  adaptSecurityPolicyToAPI({
    attempts: 8,
    blockingDuration: fiveTeenMinutes,
    canReusePasswords: false,
    delayBeforeNewPassword: oneHour,
    hasLowerCase: false,
    hasNumber: true,
    hasSpecialCharacter: true,
    hasUpperCase: false,
    passwordExpiration: sevenDays,
    passwordMinLength: 42,
  });
