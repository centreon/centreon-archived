import { SecurityPolicy } from '../models';

export const adaptSecurityPolicyFromAPI = (
  securityPolicy: SecurityPolicy,
): SecurityPolicy => {
  return {
    ...securityPolicy,
    blockingDuration: securityPolicy.blockingDuration
      ? securityPolicy.blockingDuration * 1000
      : null,
    delayBeforeNewPassword: securityPolicy.delayBeforeNewPassword
      ? securityPolicy.delayBeforeNewPassword * 1000
      : null,
    passwordExpiration: securityPolicy.passwordExpiration
      ? securityPolicy.passwordExpiration * 1000
      : null,
  };
};

export const adaptSecurityPolicyToAPI = (
  securityPolicy: SecurityPolicy,
): SecurityPolicy => {
  return {
    ...securityPolicy,
    blockingDuration: securityPolicy.blockingDuration
      ? securityPolicy.blockingDuration / 1000
      : null,
    delayBeforeNewPassword: securityPolicy.delayBeforeNewPassword
      ? securityPolicy.delayBeforeNewPassword / 1000
      : null,
    passwordExpiration: securityPolicy.passwordExpiration
      ? securityPolicy.passwordExpiration / 1000
      : null,
  };
};
