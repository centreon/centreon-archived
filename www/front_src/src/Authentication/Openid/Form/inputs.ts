import { equals } from 'ramda';

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

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    label: labelEnableOpenIDConnectAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'isForced',
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
    label: labelTrustedClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'blacklistClientAddresses',
    label: labelBlacklistClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'baseUrl',
    label: labelBaseUrl,
    type: InputType.Text,
  },
  {
    fieldName: 'authorizationEndpoint',
    label: labelAuthorizationEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'tokenEndpoint',
    label: labelTokenEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'introspectionTokenEndpoint',
    label: labelIntrospectionTokenEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'userInformationEndpoint',
    label: labelUserInformationEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'endSessionEndpoint',
    label: labelEndSessionEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'connectionScopes',
    label: labelScopes,
    type: InputType.Multiple,
  },
  {
    fieldName: 'loginClaim',
    label: labelLoginClaimValue,
    type: InputType.Text,
  },
  {
    fieldName: 'clientId',
    label: labelClientID,
    type: InputType.Text,
  },
  {
    fieldName: 'clientSecret',
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
    label: labelUseBasicAuthenticatonForTokenEndpointAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'verifyPeer',
    label: labelDisableVerifyPeer,
    type: InputType.Switch,
  },
];
