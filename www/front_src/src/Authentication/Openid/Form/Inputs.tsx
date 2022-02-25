import * as React from 'react';

import { always, cond, equals, propEq } from 'ramda';

import { makeStyles } from '@mui/styles';

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
import { AuthenticationType, InputProps, InputType } from '../models';

import SwitchInput from './Switch';
import RadioInput from './Radio';
import TextInput from './Text';
import MultiTextInput from './MultiText';

const isAuthenticationNotActive = propEq('isActive', false);

const getInput = cond<InputType, (props: InputProps) => JSX.Element>([
  [equals(InputType.Switch) as (b: InputType) => boolean, always(SwitchInput)],
  [equals(InputType.Radio) as (b: InputType) => boolean, always(RadioInput)],
  [equals(InputType.Text) as (b: InputType) => boolean, always(TextInput)],
  [
    equals(InputType.MultiText) as (b: InputType) => boolean,
    always(MultiTextInput),
  ],
  [equals(InputType.Password) as (b: InputType) => boolean, always(TextInput)],
]);

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
    type: InputType.MultiText,
  },
  {
    fieldName: 'blacklistClientAddresses',
    getDisabled: isAuthenticationNotActive,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiText,
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
    type: InputType.MultiText,
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

const useStyles = makeStyles((theme) => ({
  inputs: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

const Inputs = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.inputs}>
      {inputs.map(
        ({
          fieldName,
          label,
          getDisabled,
          type,
          options,
          change,
          getChecked,
        }) => {
          const Input = getInput(type);

          const props = {
            change,
            fieldName,
            getChecked,
            getDisabled,
            label,
            options,
            type,
          };

          return <Input key={label} {...props} />;
        },
      )}
    </div>
  );
};

export default Inputs;
