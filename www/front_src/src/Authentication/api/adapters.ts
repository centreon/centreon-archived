import { map } from 'ramda';

import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyToAPI,
} from '../Local/models';
import {
  AuthorizationRelation,
  AuthorizationRelationToAPI,
  OpenidConfiguration,
  OpenidConfigurationToAPI,
} from '../Openid/models';
import {
  WebSSOConfiguration,
  WebSSOConfigurationToAPI,
} from '../WebSSO/models';

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

const adaptAuthorizationRelationsToAPI = (
  authorizationRelations: Array<AuthorizationRelation>,
): Array<AuthorizationRelationToAPI> =>
  map(
    ({ name, accessGroup }: AuthorizationRelation) => ({
      access_group: accessGroup,
      name,
    }),
    authorizationRelations,
  );

export const adaptOpenidConfigurationToAPI = ({
  authenticationType,
  authorizationEndpoint,
  baseUrl,
  blacklistClientAddresses,
  clientId,
  clientSecret,
  connectionScopes,
  endSessionEndpoint,
  introspectionTokenEndpoint,
  isActive,
  isForced,
  loginClaim,
  tokenEndpoint,
  trustedClientAddresses,
  userinfoEndpoint,
  verifyPeer,
  autoImport,
  contactTemplate,
  emailBindAttribute,
  aliasBindAttribute,
  fullnameBindAttribute,
  contactGroup,
  authorizationClaim,
  authorizationRelations,
}: OpenidConfiguration): OpenidConfigurationToAPI => ({
  alias_bind_attribute: aliasBindAttribute || null,
  authentication_type: authenticationType || null,
  authorization_claim: authorizationClaim || null,
  authorization_endpoint: authorizationEndpoint || null,
  authorization_relations:
    adaptAuthorizationRelationsToAPI(authorizationRelations) || [],
  auto_import: autoImport,
  base_url: baseUrl || null,
  blacklist_client_addresses: blacklistClientAddresses,
  client_id: clientId || null,
  client_secret: clientSecret || null,
  connection_scopes: connectionScopes,
  contact_group: contactGroup || null,
  contact_template: contactTemplate || null,
  email_bind_attribute: emailBindAttribute || null,
  endsession_endpoint: endSessionEndpoint || null,
  fullname_bind_attribute: fullnameBindAttribute || null,
  introspection_token_endpoint: introspectionTokenEndpoint || null,
  is_active: isActive,
  is_forced: isForced,
  login_claim: loginClaim || null,
  token_endpoint: tokenEndpoint || null,
  trusted_client_addresses: trustedClientAddresses,
  userinfo_endpoint: userinfoEndpoint || null,
  verify_peer: verifyPeer,
});

export const adaptWebSSOConfigurationToAPI = ({
  loginHeaderAttribute,
  patternMatchingLogin,
  patternReplaceLogin,
  blacklistClientAddresses,
  isActive,
  isForced,
  trustedClientAddresses,
}: WebSSOConfiguration): WebSSOConfigurationToAPI => ({
  blacklist_client_addresses: blacklistClientAddresses,
  is_active: isActive,
  is_forced: isForced,
  login_header_attribute: loginHeaderAttribute || null,
  pattern_matching_login: patternMatchingLogin || null,
  pattern_replace_login: patternReplaceLogin || null,
  trusted_client_addresses: trustedClientAddresses,
});
