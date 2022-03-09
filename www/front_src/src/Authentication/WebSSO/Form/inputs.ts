import { propEq } from 'ramda';

import {
  labelBlacklistClientAddresses,
  labelMixed,
  labelWebSSOOnly,
  labelTrustedClientAddresses,
  labelLoginHeaderAttributeName,
  labelPatternMatchLogin,
  labelPatternReplaceLogin,
  labelEnableWebSSOAuthentication,
  labelAuthenticationMode,
} from '../translatedLabels';
import { InputProps, InputType } from '../../FormInputs/models';

const isAuthenticationNotActive = propEq('isActive', false);

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'isForced',
    getDisabled: isAuthenticationNotActive,
    label: labelAuthenticationMode,
    options: [
      {
        isChecked: (value: boolean): boolean => value,
        label: labelWebSSOOnly,
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
    fieldName: 'loginHeaderAttribute',
    getDisabled: isAuthenticationNotActive,
    label: labelLoginHeaderAttributeName,
    type: InputType.Text,
  },
  {
    fieldName: 'patternMatchingLogin',
    getDisabled: isAuthenticationNotActive,
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    fieldName: 'patternReplaceLogin',
    getDisabled: isAuthenticationNotActive,
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];
