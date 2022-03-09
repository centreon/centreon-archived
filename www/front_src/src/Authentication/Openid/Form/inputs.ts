import { equals, propEq } from 'ramda';

import {
  labelAuthenticationMode,
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelDisableVerifyPeer,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelIntrospectionTokenEndpoint,
  labelLoginClaimValue,
  labelMixed,
  labelOpenIDConnectOnly,
  labelScopes,
  labelTokenEndpoint,
  labelTrustedClientAddresses,
  labelUseBasicAuthenticatonForTokenEndpointAuthentication,
  labelUserInformationEndpoint,
} from '../translatedLabels';
import { AuthenticationType } from '../models';
import { InputProps, InputType } from '../../FormInputs/models';

const isAuthenticationNotActive = propEq('isActive', false);

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    label: labelEnableOpenIDConnectAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'isForced',
    getDisabled: isAuthenticationNotActive,
    label: labelAuthenticationMode,
    options: [
      {
        isChecked: (value: boolean): boolean => value,
        label: labelOpenIDConnectOnly,
        value: true,
      },
      {
        isChecked: (value: boolean): boolean => !value,
        label: labelMixed,
        value: false,
      },
    ],
    type: InputType.Radio,
  },
  {
    fieldName: 'trustedClientAddresses',
    getDisabled: isAuthenticationNotActive,
    label: labelTrustedClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'blacklistClientAddresses',
    getDisabled: isAuthenticationNotActive,
    label: labelBlacklistClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'baseUrl',
    getDisabled: isAuthenticationNotActive,
    label: labelBaseUrl,
    type: InputType.Text,
  },
  {
    fieldName: 'authorizationEndpoint',
    getDisabled: isAuthenticationNotActive,
    label: labelAuthorizationEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'tokenEndpoint',
    getDisabled: isAuthenticationNotActive,
    label: labelTokenEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'introspectionTokenEndpoint',
    getDisabled: isAuthenticationNotActive,
    label: labelIntrospectionTokenEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'userInformationEndpoint',
    getDisabled: isAuthenticationNotActive,
    label: labelUserInformationEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'endSessionEndpoint',
    getDisabled: isAuthenticationNotActive,
    label: labelEndSessionEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'connectionScopes',
    getDisabled: isAuthenticationNotActive,
    label: labelScopes,
    type: InputType.Multiple,
  },
  {
    fieldName: 'loginClaim',
    getDisabled: isAuthenticationNotActive,
    label: labelLoginClaimValue,
    type: InputType.Text,
  },
  {
    fieldName: 'clientId',
    getDisabled: isAuthenticationNotActive,
    label: labelClientID,
    type: InputType.Text,
  },
  {
    fieldName: 'clientSecret',
    getDisabled: isAuthenticationNotActive,
    label: labelClientSecret,
    type: InputType.Password,
  },
  {
    change: ({ setFieldValue, value }): void => {
      setFieldValue(
        'authenticationType',
        value
          ? AuthenticationType.ClientSecretPost
          : AuthenticationType.ClientSecretBasic,
      );
    },
    fieldName: 'authenticationType',
    getChecked: (value) => equals(AuthenticationType.ClientSecretPost, value),
    getDisabled: isAuthenticationNotActive,
    label: labelUseBasicAuthenticatonForTokenEndpointAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'verifyPeer',
    getDisabled: isAuthenticationNotActive,
    label: labelDisableVerifyPeer,
    type: InputType.Switch,
  },
];
