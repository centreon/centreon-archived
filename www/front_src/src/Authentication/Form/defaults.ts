import { SecurityPolicy } from '../models';
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
