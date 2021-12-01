import { SecurityPolicy } from '../models';
import { fiveTeenMinutes, oneHour, sevenDays } from '../timestamps';

const defaultSecurityPolicy: SecurityPolicy = {
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

export default defaultSecurityPolicy;
